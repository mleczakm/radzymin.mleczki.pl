/**
 * Heurystyczne wykrywanie danych, które mogą nie nadawać się do publikacji w jawnym repozytorium
 * (PESEL, telefon, e-mail, adres). To jest POMOC, nie gwarancja — nie zastępuje przeczytania
 * dokumentu przed publikacją. Żadnej biblioteki: to kilkanaście linii regexów, uruchamianych
 * w przeglądarce, zero danych wysyłanych na zewnątrz.
 *
 * WAŻNE dla wdrażającego ten szablon: raz opublikowany plik zostaje w historii Gita nawet po
 * "usunięciu" — to narzędzie musi zadziałać PRZED pierwszym commitem, poprawka po fakcie
 * wymaga przepisania historii repozytorium (git filter-repo / BFG), nie zwykłego nowego commita.
 */
(function (global) {
  'use strict';

  /** Suma kontrolna numeru PESEL (11 cyfr, wagi 1,3,7,9,1,3,7,9,1,3 na pierwszych dziesięciu). */
  function isValidPesel(digits) {
    if (!/^\d{11}$/.test(digits)) return false;
    var weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
    var sum = 0;
    for (var i = 0; i < 10; i++) sum += Number(digits[i]) * weights[i];
    var control = (10 - (sum % 10)) % 10;
    return control === Number(digits[10]);
  }

  var PATTERNS = [
    {
      type: 'PESEL',
      placeholder: '[PESEL]',
      // 11 cyfr pod rząd; dalej odsiewane przez sumę kontrolną, żeby nie łapać przypadkowych
      // ciągów liczb (numery spraw, kwoty).
      regex: /\b\d{11}\b/g,
      validate: isValidPesel,
    },
    {
      type: 'e-mail',
      placeholder: '[E-MAIL]',
      regex: /\b[\w.+-]+@[\w-]+\.[\w.-]+\b/g,
    },
    {
      type: 'telefon',
      placeholder: '[TELEFON]',
      regex: /\b(?:\+48[\s-]?)?(?:\d{3}[\s-]?){2}\d{3}\b/g,
    },
    {
      type: 'adres',
      placeholder: '[ADRES]',
      regex: /\b(?:ul\.|ulica|al\.|aleja|pl\.|plac)\s+[A-ZŁŚŻŹĆŃÓĄĘ][\wąćęłńóśźż-]*(?:\s+[A-ZŁŚŻŹĆŃÓĄĘ]?[\wąćęłńóśźż-]*)?\s+\d+[a-zA-Z]?(?:\/\d+)?\b/g,
    },
  ];

  /**
   * @param {string} text
   * @returns {{type: string, match: string}[]} znalezione fragmenty, każdy raz
   */
  function scanText(text) {
    if (!text) return [];
    var found = [];
    var seen = new Set();
    PATTERNS.forEach(function (p) {
      var m;
      p.regex.lastIndex = 0;
      while ((m = p.regex.exec(text))) {
        if (p.validate && !p.validate(m[0])) continue;
        var key = p.type + ':' + m[0];
        if (seen.has(key)) continue;
        seen.add(key);
        found.push({ type: p.type, match: m[0] });
      }
    });
    return found;
  }

  /**
   * Wzorzec dla frazy podanej ręcznie (np. nazwisko). Polskie nazwiska się odmieniają
   * (Kowalski -> Kowalskiego), więc dopasowujemy temat bez ostatnich dwóch liter razem z
   * dowolną końcówką. Granice słów przez \p{L}, bo \b nie zna polskich liter.
   */
  function phraseRegex(phrase) {
    var clean = phrase.trim();
    if (clean.length < 3) return null;
    var stem = clean.length > 4 ? clean.slice(0, -2) : clean;
    var escaped = stem.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return new RegExp('(?<![\\p{L}\\p{N}])' + escaped + '\\p{L}*', 'giu');
  }

  /**
   * Zamienia wykryte dane na znaczniki ([PESEL], [E-MAIL]...) oraz podane frazy na [DANE].
   * @param {string} text
   * @param {string[]} [extraPhrases] imiona, nazwiska itp., których wzorce nie wykryją
   * @returns {{text: string, replaced: {type: string, match: string}[]}}
   */
  function anonymizeText(text, extraPhrases) {
    var replaced = [];
    var out = text || '';
    PATTERNS.forEach(function (p) {
      p.regex.lastIndex = 0;
      out = out.replace(p.regex, function (m) {
        if (p.validate && !p.validate(m)) return m;
        replaced.push({ type: p.type, match: m });
        return p.placeholder;
      });
    });
    (extraPhrases || []).forEach(function (phrase) {
      var re = phraseRegex(phrase);
      if (!re) return;
      out = out.replace(re, function (m) {
        replaced.push({ type: 'fraza', match: m });
        return '[DANE]';
      });
    });
    return { text: out, replaced: replaced };
  }

  /**
   * Przedziały [start, end) wykrytych danych w tekście — do zamalowania w PDF, gdzie liczy się
   * położenie, a nie sama treść. Te same wzorce i frazy co w anonymizeText.
   * @returns {{start: number, end: number, type: string}[]}
   */
  function findRanges(text, extraPhrases) {
    var ranges = [];
    var src = text || '';
    PATTERNS.forEach(function (p) {
      var m;
      p.regex.lastIndex = 0;
      while ((m = p.regex.exec(src))) {
        if (p.validate && !p.validate(m[0])) continue;
        ranges.push({ start: m.index, end: m.index + m[0].length, type: p.type });
      }
    });
    (extraPhrases || []).forEach(function (phrase) {
      var re = phraseRegex(phrase);
      var m;
      while (re && (m = re.exec(src))) {
        ranges.push({ start: m.index, end: m.index + m[0].length, type: 'fraza' });
      }
    });
    // Zlicz nakładające się trafienia (np. imię w adresie e-mail) jako jeden przedział.
    ranges.sort(function (a, b) { return a.start - b.start || b.end - a.end; });
    return ranges.reduce(function (merged, r) {
      var last = merged[merged.length - 1];
      if (last && r.start <= last.end) last.end = Math.max(last.end, r.end);
      else merged.push({ start: r.start, end: r.end, type: r.type });
      return merged;
    }, []);
  }

  /** Zbiera wszystkie ciągi znaków z (zagnieżdżonych) danych wpisu, np. opisy w liście kroków. */
  function collectStrings(value, acc) {
    if (typeof value === 'string') acc.push(value);
    else if (Array.isArray(value)) value.forEach(function (v) { collectStrings(v, acc); });
    else if (value && typeof value === 'object') {
      Object.keys(value).forEach(function (k) { collectStrings(value[k], acc); });
    }
    return acc;
  }

  /** Skanuje wszystkie pola tekstowe wpisu Decap CMS (Immutable Map), także zagnieżdżone. */
  function scanEntryData(dataMap) {
    var plain = dataMap && dataMap.toJS ? dataMap.toJS() : dataMap;
    var all = [];
    collectStrings(plain, []).forEach(function (str) {
      all = all.concat(scanText(str));
    });
    return all;
  }

  global.PiiCheck = { scanText: scanText, scanEntryData: scanEntryData, anonymizeText: anonymizeText, findRanges: findRanges };
})(window);
