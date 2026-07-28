// moment.js locale configuration
// locale : Slovenian (sl)
// author : Robert Sedovšek : https://github.com/sedovsek

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['moment'], factory); // AMD
    } else if (typeof exports === 'object') {
        module.exports = factory(require('../moment')); // Node
    } else {
        factory((typeof global !== 'undefined' ? global : this).moment); // node or other global
    }
}(function (moment) {
    function processRelativeTime(number, withoutSuffix, key, isFuture) {
        var format = {
            'm': ['ena minuta', 'eno minuto'],
            'h': ['ena ura', 'eno uro'],
            'd': ['en dan', 'enim dnem'],
            'dd': [number + ' dan', number + ' dnem'],
            'M': ['en mesec', 'enim mesecem'],
            'MM': [number + ' mesec', number + ' mesecem'],
            'y': ['eno leto', 'enim letom'],
            'yy': [number + ' leto', number + ' letom']
        };
        return withoutSuffix ? format[key][0] : format[key][1];
    }

    return moment.defineLocale('de', {
        months : 'januar_februar_marec_april_maj_junij_julij_avgust_september_oktober_november_december'.split('_'),
        monthsShort : 'jan._feb._mar._apr._maj._jun._jul._avg._sep._okt._nov._dec.'.split('_'),
        weekdays : 'nedelja_ponedeljek_torek_sreda_četrtek_petek_sobota'.split('_'),
        weekdaysShort : 'ned._pon._tor._sre._čet._pet._sob.'.split('_'),
        weekdaysMin : 'ne_po_to_sr_če_pe_so'.split('_'),
        longDateFormat : {
            LT: 'H:mm',
            LTS: 'H:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D. MMMM YYYY',
            LLL : 'D. MMMM YYYY LT',
            LLLL : 'dddd, D. MMMM YYYY LT'
        },
        calendar : {
            sameDay: '[danes ob] LT',
            sameElse: 'L',
            nextDay: '[jutri ob] LT',
            nextWeek: '[v] dddd [ob] LT',
            lastDay: '[včeraj ob] LT',
            lastWeek: '[prejšnji] dddd [ob] LT'
        },
        relativeTime : {
            future : 'čez %s',
            past : 'pred %s',
            s : 'nekaj sekund',
            m : processRelativeTime,
            mm : '%d minut',
            h : processRelativeTime,
            hh : '%d ur',
            d : processRelativeTime,
            dd : processRelativeTime,
            M : processRelativeTime,
            MM : processRelativeTime,
            y : processRelativeTime,
            yy : processRelativeTime
        },
        ordinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });
}));