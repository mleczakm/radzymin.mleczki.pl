#!/usr/bin/env python3
"""Reconcile one Cloudflare Email Routing address without touching other rules."""

import argparse
import json
import os
import subprocess
import sys


def cf(*arguments):
    result = subprocess.run(
        ["cf", *arguments],
        capture_output=True,
        text=True,
        check=False,
        env=os.environ.copy(),
    )
    if result.returncode != 0:
        raise RuntimeError(f"cf {' '.join(arguments[:3])} failed: {result.stderr.strip()}")
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError as error:
        raise RuntimeError("Cloudflare CLI did not return JSON") from error


def matching_rules(rules, address):
    return [
        rule
        for rule in rules
        if any(
            matcher.get("type") == "literal"
            and matcher.get("field") == "to"
            and matcher.get("value") == address
            for matcher in rule.get("matchers", [])
        )
    ]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--zone", required=True)
    parser.add_argument("--subdomain", required=True)
    parser.add_argument("--address", required=True)
    parser.add_argument("--forward-to", required=True)
    args = parser.parse_args()

    if args.address.rsplit("@", 1)[-1] != args.subdomain:
        raise ValueError("Routing address must belong to the configured subdomain")

    zone_args = ("-z", args.zone)
    settings = cf("email-routing", "settings", "get", *zone_args)
    subdomain = next(
        (item for item in settings.get("subdomains", []) if item.get("name") == args.subdomain),
        None,
    )
    changed = False
    if subdomain is None or not subdomain.get("enabled") or subdomain.get("status") != "ready":
        cf("email-routing", "enable", *zone_args, "--name", args.subdomain)
        changed = True

    desired = {
        "name": "Radzymin contact",
        "enabled": True,
        "matchers": [{"type": "literal", "field": "to", "value": args.address}],
        "actions": [{"type": "forward", "value": [args.forward_to]}],
    }
    rules = matching_rules(cf("email-routing", "rules", "list-account", *zone_args), args.address)
    if len(rules) > 1:
        raise RuntimeError(f"Multiple Cloudflare rules match {args.address}; resolve manually")
    if not rules:
        cf("email-routing", "rules", "create", *zone_args, "--body", json.dumps(desired))
        changed = True
    else:
        rule = rules[0]
        if any(rule.get(key) != desired[key] for key in desired):
            if rule.get("source") != "api":
                raise RuntimeError("The matching rule is managed by another source")
            cf(
                "email-routing", "rules", "update", rule["id"], *zone_args,
                "--body", json.dumps(desired),
            )
            changed = True

    settings = cf("email-routing", "settings", "get", *zone_args)
    subdomain = next(
        (item for item in settings.get("subdomains", []) if item.get("name") == args.subdomain),
        None,
    )
    if subdomain is None or not subdomain.get("enabled") or subdomain.get("status") != "ready":
        raise RuntimeError(f"Email Routing for {args.subdomain} is not ready")
    rules = matching_rules(cf("email-routing", "rules", "list-account", *zone_args), args.address)
    if len(rules) != 1 or any(rules[0].get(key) != desired[key] for key in desired):
        raise RuntimeError(f"Routing rule for {args.address} is not in the desired state")

    print(json.dumps({"changed": changed, "address": args.address, "forward_to": args.forward_to}))


if __name__ == "__main__":
    try:
        main()
    except (RuntimeError, ValueError) as error:
        print(str(error), file=sys.stderr)
        sys.exit(1)
