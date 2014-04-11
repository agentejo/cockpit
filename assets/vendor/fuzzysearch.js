/*
  * copyright: https://github.com/atom/fuzzaldrin - https://github.com/atom/fuzzaldrin/blob/master/LICENSE.md
 */

(function(global){

    var filter, scorer, FuzzySearch;

    /*
    Original ported from:

    string_score.js: String Scoring Algorithm 0.1.10

    http://joshaven.com/string_score
    https://github.com/joshaven/string_score

    Copyright (C) 2009-2011 Joshaven Potter <yourtech@gmail.com>
    Special thanks to all of the contributors listed here https://github.com/joshaven/string_score
    MIT license: http://www.opensource.org/licenses/mit-license.php

    Date: Tue Mar 1 2011
    */

    scorer = {

        basenameScore: function(string, query, score) {

            var base = null, depth, index, lastCharacter, segmentCount, slashCount = 0;

            index = string.length - 1;

            while (string[index] === '/') {
                index--;
            }

            lastCharacter = index;

            while (index >= 0) {
                if (string[index] === '/') {

                    slashCount++;
                    if (base == null) {
                        base = string.substring(index + 1, lastCharacter + 1);
                    }

                } else if (index === 0) {

                    if (lastCharacter < string.length - 1) {

                        if (base == null) {
                            base = string.substring(0, lastCharacter + 1);
                        }
                    } else {

                        if (base == null) {
                            base = string;
                        }
                    }
                }
                index--;
            }

            if (base === string) {
                score *= 2;
            } else if (base) {
                score += this.score(base, query);
            }

            segmentCount = slashCount + 1;
            depth        = Math.max(1, 10 - segmentCount);
            score       *= depth * 0.01;

            return score;
        },

        score: function(string, query) {

            var character, characterScore, indexInQuery, indexInString, lowerCaseIndex, minIndex, queryLength, queryScore, stringLength, totalCharacterScore, upperCaseIndex, _ref;

            if (string === query) {
                return 1;
            }

            totalCharacterScore = 0;
            queryLength         = query.length;
            stringLength        = string.length;
            indexInQuery        = 0;
            indexInString       = 0;

            while (indexInQuery < queryLength) {

                character      = query[indexInQuery++];
                lowerCaseIndex = string.indexOf(character.toLowerCase());
                upperCaseIndex = string.indexOf(character.toUpperCase());
                minIndex       = Math.min(lowerCaseIndex, upperCaseIndex);

                if (minIndex === -1) {
                    minIndex = Math.max(lowerCaseIndex, upperCaseIndex);
                }

                indexInString = minIndex;

                if (indexInString === -1) {
                    return 0;
                }

                characterScore = 0.1;

                if (string[indexInString] === character) {
                    characterScore += 0.1;
                }

                if (indexInString === 0 || string[indexInString - 1] === '/') {
                    characterScore += 0.8;
                } else if ((_ref = string[indexInString - 1]) === '-' || _ref === '_' || _ref === ' ') {
                    characterScore += 0.7;
                }

                string = string.substring(indexInString + 1, stringLength);
                totalCharacterScore += characterScore;
            }

            queryScore = totalCharacterScore / queryLength;
            return ((queryScore * (queryLength / stringLength)) + queryScore) / 2;
        }
    };

    filter = function(candidates, query, queryHasSlashes, _arg) {

        var candidate, key, maxResults, score, scoredCandidate, scoredCandidates, string, _i, _len, _ref;

        _ref = _arg != null ? _arg : {}, key = _ref.key, maxResults = _ref.maxResults;

        if (query) {
            scoredCandidates = [];
            for (_i = 0, _len = candidates.length; _i < _len; _i++) {
                candidate = candidates[_i];
                string = key != null ? candidate[key] : candidate;

                if (!string) {
                    continue;
                }

                score = scorer.score(string, query, queryHasSlashes);

                if (!queryHasSlashes) {
                    score = scorer.basenameScore(string, query, score);
                }

                if (score > 0) {
                    scoredCandidates.push({
                        candidate: candidate,
                        score: score
                    });
                }
            }

            scoredCandidates.sort(function(a, b) {
                return b.score - a.score;
            });

            candidates = (function() {
                var _j, _len1, _results = [];

                for (_j = 0, _len1 = scoredCandidates.length; _j < _len1; _j++) {
                    scoredCandidate = scoredCandidates[_j];
                    _results.push(scoredCandidate.candidate);
                }
                return _results;
            })();
        }

        if (maxResults != null) {
            candidates = candidates.slice(0, maxResults);
        }

        return candidates;
    };

    FuzzySearch = {

        filter: function(candidates, query, options) {

            var queryHasSlashes;

            if (query) {
                queryHasSlashes = query.indexOf('/') !== -1;
                query = query.replace(/\ /g, '');
            }

            return filter(candidates, query, queryHasSlashes, options);
        },

        score: function(string, query) {

            var queryHasSlashes, score;

            if (!string || !query) {
                return 0;
            }

            if (string === query) {
                return 2;
            }

            queryHasSlashes = query.indexOf('/') !== -1;
            query           = query.replace(/\ /g, '');
            score           = scorer.score(string, query);

            if (!queryHasSlashes) {
                score = scorer.basenameScore(string, query, score);
            }

            return score;
        }
    };


    // AMD support
    if (typeof define === 'function' && define.amd) {
        define(function () { return FuzzySearch; });
    // CommonJS and Node.js module support.
    } else if (typeof exports !== 'undefined') {
        // Support Node.js specific `module.exports` (which can be a function)
        if (typeof module != 'undefined' && module.exports) {
        exports = module.exports = FuzzySearch;
    }
        // But always support CommonJS module 1.1.1 spec (`exports` cannot be a function)
        exports.FuzzySearch = FuzzySearch;
    } else {
        global.FuzzySearch = FuzzySearch;
    }

})(this);