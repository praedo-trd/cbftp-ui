
// Hack the ace definition of define and make it global
//window.define = window.define || ace.define;

const Ace = require('ace-builds/src-noconflict/ace');

Ace.define('ace/mode/trd', function(require, exports, module) {
  // var oop = require("ace/lib/oop");
  
  const oop = Ace.require("ace/lib/oop")
  const TextMode = Ace.require("ace/mode/text").Mode;

  
  // var TextMode = require("ace/mode/text").Mode;
  const Tokenizer = Ace.require("ace/tokenizer").Tokenizer;
  const TRDHighlightRules = Ace.require("ace/mode/trd_highlight_rules").TRDHighlightRules;

  var Mode = function() {
    this.$tokenizer = new Tokenizer(new TRDHighlightRules().getRules());
  };
  oop.inherits(Mode, TextMode);

  (function() {
    // Extra logic goes here. (see below)
  }).call(Mode.prototype);

  exports.Mode = Mode;
});

Ace.define('ace/mode/trd_highlight_rules', function(require, exports, module) {

  const oop = Ace.require("ace/lib/oop")
  const TextHighlightRules = Ace.require("ace/mode/text_highlight_rules").TextHighlightRules;

  const TRDHighlightRules = function() {

    var keywordMapper = this.createKeywordMapper({
        "variable.language": "this",
        "support.function": "ALLOW DROP EXCEPT",
        "keyword":
            "AND OR",
        "constant.boolean":
            "true false",
        "keyword.operator":
            "containsany !containsany isin !isin contains !contains iswm !iswm"
    }, "text", true, " ");

    this.$rules = {
        "start" : [
            {token : keywordMapper, regex : "\\b\\w+\\b"},
            {token : "comment", regex : "#.*$"},
            {token : "variable", regex: "\\[\\S+\\]"},
            {token : "keyword.operator", regex: /\W(?:==|\!=|>|<|>=|<=)\W/},
            {token : "variable.parameter.wildcard", regex : "[*]\\S+[*]|[*]\\S+|\\S+[*]"},
            {caseInsensitive: true}
        ]
    };

  };

  oop.inherits(TRDHighlightRules, TextHighlightRules);

  exports.TRDHighlightRules = TRDHighlightRules;
});
