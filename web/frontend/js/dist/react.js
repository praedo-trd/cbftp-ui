"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var AutoRules = function (_React$Component) {
  _inherits(AutoRules, _React$Component);

  function AutoRules(props) {
    _classCallCheck(this, AutoRules);

    var _this = _possibleConstructorReturn(this, (AutoRules.__proto__ || Object.getPrototypeOf(AutoRules)).call(this, props));

    _this.state = {
      rules: [],
      schedule: {
        1: ["00:00", "23:59"],
        2: ["00:00", "23:59"],
        3: ["00:00", "23:59"],
        4: ["00:00", "23:59"],
        5: ["00:00", "23:59"],
        6: ["00:00", "23:59"],
        7: ["00:00", "23:59"]
      },
      loaded: false
    };
    return _this;
  }

  _createClass(AutoRules, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var that = this;
      fetch('/api/autorules', { method: 'get' }).then(function (response) {
        return response.json().then(function (json) {
          console.log(json);
          that.setState({
            rules: json.rules,
            schedule: json.schedule,
            loaded: true
          });
        });
      }).catch(function (err) {
        // Error :(
      });
    }
  }, {
    key: "onChangeRules",
    value: function onChangeRules(rules) {
      var newState = JSON.parse(JSON.stringify(this.state.rules));

      var value = _.without(rules.trim().split("\n"), "");
      _.set(newState, "rules", value);

      this.setState({
        rules: newState.rules
      });
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState) {

      if (prevState.loaded === false) {
        return;
      }

      var payload = new FormData();
      payload.append('rules', this.state.rules);

      // var data = new FormData();
      // data.append( "json", JSON.stringify( payload ) );

      fetch("/api/autorules/save", {
        method: "POST",
        body: JSON.stringify({ rules: this.state.rules, schedule: this.state.schedule }),
        headers: new Headers({
          'Content-Type': 'application/json'
        })
      }).then(function (res) {
        return res.json();
      }).then(function (data) {
        console.log(data);
      });
    }
  }, {
    key: "onChangeSchedule",
    value: function onChangeSchedule(dayIndex, dayIndexKey, e) {
      var newState = jQuery.extend(true, {}, this.state.schedule);
      var newState = JSON.parse(JSON.stringify(this.state.schedule));

      _.set(newState, dayIndex + ".[" + dayIndexKey + "]", e.target.value);
      console.log(newState);

      this.setState({
        schedule: newState
      });
    }
  }, {
    key: "render",
    value: function render() {
      var that = this;

      if (!this.state.loaded) {
        return React.createElement(
          "p",
          null,
          "Loading..."
        );
      }

      var styles = {
        width: '100%'
      };

      var rules = "";

      if (typeof this.state.rules != 'undefined') {
        rules = this.state.rules.join("\n");
      }

      var styles = {
        width: "60px"
      };

      var spacer = {
        display: "inline-block",
        width: "80px"
      };

      var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"].map(function (row, idx) {
        return React.createElement(
          "tr",
          { key: row },
          React.createElement(
            "td",
            null,
            row
          ),
          React.createElement(
            "td",
            null,
            React.createElement("input", {
              onChange: that.onChangeSchedule.bind(that, idx + 1, 0),
              type: "text",
              className: "form-control input-sm",
              style: { width: "70px", marginRight: 0 },
              defaultValue: that.state.schedule[idx + 1][0]
            }),
            " -",
            React.createElement("input", {
              onChange: that.onChangeSchedule.bind(that, idx + 1, 1),
              type: "text",
              className: "form-control input-sm",
              style: { width: "70px" },
              defaultValue: that.state.schedule[idx + 1][1]
            })
          )
        );
      });

      return React.createElement(
        "div",
        { className: "well" },
        React.createElement(
          "h3",
          null,
          "Autotrading Rules"
        ),
        React.createElement(
          "div",
          { className: "row" },
          React.createElement(
            "div",
            { className: "col-md-3 form-inline" },
            React.createElement(
              "p",
              null,
              "Schedule:"
            ),
            React.createElement(
              "table",
              { className: "table table-bordered" },
              React.createElement(
                "tbody",
                null,
                days
              )
            )
          ),
          React.createElement(
            "div",
            { className: "col-md-9" },
            React.createElement(
              "p",
              null,
              "One rule per line"
            ),
            React.createElement(
              "div",
              { className: "form-group" },
              React.createElement(RulesEditor, { key: "rules", defaultValue: rules, onChange: this.onChangeRules.bind(this) })
            ),
            React.createElement(
              "div",
              { className: "alert alert-warning" },
              "You can use the special variable ",
              React.createElement(
                "code",
                null,
                "[chain]"
              ),
              " only on this page to write auto rules based on which sites are in the chain."
            )
          )
        )
      );
    }
  }]);

  return AutoRules;
}(React.Component);
//# sourceMappingURL=AutoRules.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var AutoSettings = function (_React$Component) {
  _inherits(AutoSettings, _React$Component);

  function AutoSettings(props) {
    _classCallCheck(this, AutoSettings);

    var _this = _possibleConstructorReturn(this, (AutoSettings.__proto__ || Object.getPrototypeOf(AutoSettings)).call(this, props));

    _this.classifications = ["Animation", "Award Show", "Documentary", "Game Show", "News", "Reality", "Scripted", "Sports", "Talk Show", "Variety"];

    _this.countries = ["United States", "Canada", "United Kingdom", "Australia", "New Zealand", "Denmark", "Norway", "Sweden"];

    _this.state = {
      setClassifications: [],
      setCountries: [],
      loaded: false
    };
    return _this;
  }

  _createClass(AutoSettings, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var that = this;
      fetch('/api/auto/settings/' + this.props.section, { method: 'get' }).then(function (response) {
        return response.json().then(function (json) {
          that.setState({
            setClassifications: json.allowed_classifications,
            setCountries: json.allowed_countries,
            loaded: true
          });
        });
      }).catch(function (err) {
        // Error :(
      });
    }
  }, {
    key: "onChangeAllowedClassifications",
    value: function onChangeAllowedClassifications(e) {
      var classifications = this.state.setClassifications || [];
      var classification = e.target.value;
      if (!e.target.checked) {
        _.remove(classifications, function (c) {
          return c === classification;
        });
      } else {
        classifications.push(classification);
        classifications.sort();
      }
      this.setState({
        setClassifications: classifications
      });
    }
  }, {
    key: "onChangeAllowedCountries",
    value: function onChangeAllowedCountries(e) {
      var countries = this.state.setCountries || [];
      var country = e.target.value;
      if (!e.target.checked) {
        _.remove(countries, function (c) {
          return c === country;
        });
      } else {
        countries.push(country);
        countries.sort();
      }
      this.setState({
        setCountries: countries
      });
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState) {

      if (prevState.loaded === false) {
        return;
      }

      var payload = new FormData();
      payload.append('allowed_classifications', this.state.setClassifications);
      payload.append('allowed_countries', this.state.setCountries);

      // var data = new FormData();
      // data.append( "json", JSON.stringify( payload ) );

      fetch("/api/auto/settings/" + this.props.section, {
        method: "POST",
        body: payload
      }).then(function (res) {
        return res.json();
      }).then(function (data) {
        console.log(data);
      });
    }
  }, {
    key: "render",
    value: function render() {
      var that = this;
      return React.createElement(
        "div",
        { className: "well" },
        React.createElement(
          "h3",
          null,
          "Settings"
        ),
        React.createElement(
          "div",
          { className: "form-group" },
          React.createElement(
            "label",
            { className: "control-label" },
            "Allowed classifications:"
          ),
          React.createElement("br", null),
          that.classifications.map(function (classification) {

            var checked = false;
            if (that.state.setClassifications.indexOf(classification) > -1) {
              checked = true;
            }

            return React.createElement(
              "label",
              { className: "checkbox-inline", key: classification },
              React.createElement("input", { onClick: that.onChangeAllowedClassifications.bind(that), type: "checkbox", value: classification, checked: checked }),
              " ",
              classification
            );
          })
        ),
        React.createElement(
          "div",
          { className: "form-group" },
          React.createElement(
            "label",
            { className: "control-label" },
            "Allowed countries:"
          ),
          React.createElement("br", null),
          that.countries.map(function (country) {

            var checked = false;
            if (that.state.setCountries.indexOf(country) > -1) {
              checked = true;
            }

            return React.createElement(
              "label",
              { className: "checkbox-inline", key: country },
              React.createElement("input", { onClick: that.onChangeAllowedCountries.bind(that), type: "checkbox", value: country, checked: checked }),
              " ",
              country
            );
          })
        )
      );
    }
  }]);

  return AutoSettings;
}(React.Component);
//# sourceMappingURL=AutoSettings.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var InputBooleanChoice = function (_React$Component) {
    _inherits(InputBooleanChoice, _React$Component);

    function InputBooleanChoice(props) {
        _classCallCheck(this, InputBooleanChoice);

        var _this = _possibleConstructorReturn(this, (InputBooleanChoice.__proto__ || Object.getPrototypeOf(InputBooleanChoice)).call(this, props));

        _this.state = {
            selectedOption: _this.props.defaultValue
        };
        return _this;
    }

    _createClass(InputBooleanChoice, [{
        key: "handleOptionChange",
        value: function handleOptionChange(changeEvent) {
            var val = changeEvent.target.value == 'true';
            this.setState({
                selectedOption: val
            });
            this.props.onChange(this.props.name, changeEvent);
        }
    }, {
        key: "render",
        value: function render() {

            return React.createElement(
                "div",
                { className: "form-group" },
                React.createElement(
                    "label",
                    { className: "col-sm-3 control-label" },
                    this.props.label
                ),
                React.createElement(
                    "div",
                    { className: "col-sm-9" },
                    React.createElement(
                        "label",
                        { className: "radio-inline" },
                        React.createElement("input", {
                            name: this.props.name,
                            type: "radio",
                            value: "true",
                            checked: this.state.selectedOption == true,
                            onChange: this.handleOptionChange.bind(this)
                        }),
                        "True"
                    ),
                    React.createElement(
                        "label",
                        { className: "radio-inline" },
                        React.createElement("input", {
                            name: this.props.name,
                            type: "radio",
                            value: "false",
                            checked: this.state.selectedOption == false,
                            onChange: this.handleOptionChange.bind(this)
                        }),
                        "False"
                    )
                )
            );
        }
    }]);

    return InputBooleanChoice;
}(React.Component);
//# sourceMappingURL=InputBooleanChoice.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var InputTagOption = function (_React$Component) {
    _inherits(InputTagOption, _React$Component);

    function InputTagOption(props) {
        _classCallCheck(this, InputTagOption);

        return _possibleConstructorReturn(this, (InputTagOption.__proto__ || Object.getPrototypeOf(InputTagOption)).call(this, props));
    }

    _createClass(InputTagOption, [{
        key: "handleChange",
        value: function handleChange(changeEvent) {
            this.props.onChange(this.props.name, changeEvent);
        }
    }, {
        key: "onChangeSetting",
        value: function onChangeSetting(key, event) {
            this.props.onChange("tag_options." + this.props.tag + "." + key, event);
        }
    }, {
        key: "render",
        value: function render() {

            var data_sources = [];
            if (this.props.info.data_sources) {
                data_sources = this.props.info.data_sources.sort().join(" ");
            }

            var allowed_groups = [];
            if (this.props.info.allowed_groups) {
                allowed_groups = this.props.info.allowed_groups.sort().join(" ");
            }

            var tag_requires = [];
            if (this.props.info.tag_requires) {
                tag_requires = this.props.info.tag_requires.sort().join("\n");
            }

            var tag_skiplist = [];
            if (this.props.info.tag_skiplist) {
                tag_skiplist = this.props.info.tag_skiplist.sort().join("\n");
            }

            return React.createElement(
                "div",
                null,
                React.createElement(InputText, {
                    name: "data_sources",
                    label: "Data providers (space-seperator)",
                    description: "Bla bla",
                    defaultValue: data_sources,
                    onChange: this.onChangeSetting.bind(this)
                }),
                React.createElement(InputText, {
                    name: "allowed_groups",
                    label: "Allowed groups (space-seperator",
                    description: "Bla bla",
                    defaultValue: allowed_groups,
                    onChange: this.onChangeSetting.bind(this)
                }),
                React.createElement(InputTextarea, {
                    name: "tag_requires",
                    label: "Tag requires (one regex p/line)",
                    description: "Bla bla",
                    defaultValue: tag_requires,
                    onChange: this.onChangeSetting.bind(this),
                    rows: 10,
                    classes: "code"
                }),
                React.createElement(InputTextarea, {
                    name: "tag_skiplist",
                    label: "Tag skiplist (one regex p/line)",
                    description: "Bla bla",
                    defaultValue: tag_skiplist,
                    onChange: this.onChangeSetting.bind(this),
                    rows: 10,
                    classes: "code"
                })
            );
        }
    }]);

    return InputTagOption;
}(React.Component);
//# sourceMappingURL=InputTagOption.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var InputText = function (_React$Component) {
    _inherits(InputText, _React$Component);

    function InputText(props) {
        _classCallCheck(this, InputText);

        return _possibleConstructorReturn(this, (InputText.__proto__ || Object.getPrototypeOf(InputText)).call(this, props));
    }

    _createClass(InputText, [{
        key: "handleChange",
        value: function handleChange(changeEvent) {
            this.props.onChange(this.props.name, changeEvent);
        }
    }, {
        key: "render",
        value: function render() {

            var classes = classNames('form-control', this.props.classes);

            return React.createElement(
                "div",
                { className: "form-group" },
                React.createElement(
                    "label",
                    { className: "col-sm-3 control-label" },
                    this.props.label
                ),
                React.createElement(
                    "div",
                    { className: "col-sm-9" },
                    React.createElement("input", {
                        name: this.props.name,
                        type: "text",
                        className: classes,
                        defaultValue: this.props.defaultValue,
                        onChange: this.handleChange.bind(this)
                    })
                )
            );
        }
    }]);

    return InputText;
}(React.Component);
//# sourceMappingURL=InputText.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var InputTextarea = function (_React$Component) {
    _inherits(InputTextarea, _React$Component);

    function InputTextarea(props) {
        _classCallCheck(this, InputTextarea);

        return _possibleConstructorReturn(this, (InputTextarea.__proto__ || Object.getPrototypeOf(InputTextarea)).call(this, props));
    }

    _createClass(InputTextarea, [{
        key: "handleChange",
        value: function handleChange(changeEvent) {
            this.props.onChange(this.props.name, changeEvent);
        }
    }, {
        key: "render",
        value: function render() {

            var classes = classNames('form-control', this.props.classes);

            var label = null;
            if (this.props.label) {
                return React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "label",
                        { className: "col-sm-3 control-label" },
                        this.props.label
                    ),
                    React.createElement(
                        "div",
                        { className: "col-sm-9" },
                        React.createElement("textarea", {
                            name: this.props.name,
                            className: classes,
                            defaultValue: this.props.defaultValue,
                            onChange: this.handleChange.bind(this),
                            rows: this.props.rows
                        })
                    )
                );
            }

            return React.createElement(
                "div",
                { className: "form-group" },
                React.createElement("textarea", {
                    name: this.props.name,
                    className: classes,
                    defaultValue: this.props.defaultValue,
                    onChange: this.handleChange.bind(this),
                    rows: this.props.rows
                })
            );
        }
    }]);

    return InputTextarea;
}(React.Component);

InputTextarea.defaultProps = {
    rows: 5
};
//# sourceMappingURL=InputTextarea.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var RulesEditor = function (_React$Component) {
  _inherits(RulesEditor, _React$Component);

  function RulesEditor(props) {
    _classCallCheck(this, RulesEditor);

    var _this = _possibleConstructorReturn(this, (RulesEditor.__proto__ || Object.getPrototypeOf(RulesEditor)).call(this, props));

    _this._onChange = _this.onChange.bind(_this);

    _this.state = {
      changes: 0
    };
    return _this;
  }

  _createClass(RulesEditor, [{
    key: "componentDidMount",
    value: function componentDidMount() {

      ace.config.set("modePath", "/frontend/js/misc/ace/mode/");
      this.editor = ace.edit(this.props.name);
      this.editor.$blockScrolling = Infinity;
      this.editor.getSession().setMode('ace/mode/' + this.props.mode);
      this.editor.setTheme('ace/theme/' + this.props.theme);
      this.editor.on('change', this.onChange.bind(this));
      this.editor.setValue(this.props.defaultValue || this.props.value, this.props.selectFirstLine === true ? -1 : null);
      this.editor.setOption('maxLines', this.props.maxLines);
      this.editor.setOption('readOnly', this.props.readOnly);
      this.editor.setOption('highlightActiveLine', this.props.highlightActiveLine);
      this.editor.setShowPrintMargin(this.props.setShowPrintMargin);
      this.editor.getSession().setUseWrapMode(this.props.wrapEnabled);
      this.editor.renderer.setShowGutter(this.props.showGutter);

      if (this.props.onLoad) {
        this.props.onLoad(this.editor);
      }
    }
  }, {
    key: "componentWillReceiveProps",
    value: function componentWillReceiveProps(nextProps) {
      var currentRange = this.editor.selection.getRange();

      // if (nextProps.value && this.editor.getValue() !== nextProps.value) {
      //     this.editor.setValue(nextProps.value, (this.props.selectFirstLine === true ? -1 : null));
      //     if(currentRange && typeof currentRange === "object") {
      //         this.editor.getSession().getSelection().setSelectionRange(currentRange);
      //     }
      // }
      if (nextProps.key !== this.props.key) {
        this.editor.setValue(nextProps.defaultValue);
      }
    }
  }, {
    key: "onChange",
    value: function onChange() {
      if (this.state.changes > 0) {
        var value = this.editor.getValue();
        this.props.onChange(value);
      }
      this.setState({
        changes: this.state.changes + 1
      });
    }
  }, {
    key: "render",
    value: function render() {
      var divStyle = {
        width: this.props.width,
        height: this.props.height
      };

      return React.DOM.div({
        id: this.props.name,
        onChange: this._onChange,
        style: divStyle
      });
    }
  }]);

  return RulesEditor;
}(React.Component);

RulesEditor.defaultProps = {
  id: "editor",
  name: "editor",
  width: "100%",
  height: "400px",
  mode: "trd",
  theme: "monokai",
  defaultValue: '',
  value: '',
  fontSize: 12,
  showGutter: true,
  onChange: null,
  onLoad: null,
  maxLines: null,
  readOnly: false,
  highlightActiveLine: true,
  showPrintMargin: true,
  selectFirstLine: false,
  wrapEnabled: false
};
//# sourceMappingURL=RulesEditor.js.map

'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SettingsEditor = function (_React$Component) {
    _inherits(SettingsEditor, _React$Component);

    function SettingsEditor(props) {
        _classCallCheck(this, SettingsEditor);

        var _this = _possibleConstructorReturn(this, (SettingsEditor.__proto__ || Object.getPrototypeOf(SettingsEditor)).call(this, props));

        _this.state = {
            info: null,
            activeSection: null
        };
        return _this;
    }

    _createClass(SettingsEditor, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            var that = this;

            fetch('/api/settings', { method: 'get' }).then(function (response) {
                return response.json().then(function (json) {
                    that.setState({
                        info: json,
                        activeTag: Object.keys(json.tag_options).sort()[0]
                    });
                });
            }).catch(function (err) {
                // Error :(
            });

            // $.getJSON("/api/settings", function(data) {
            //     that.setState({
            //         info: data
            //         ,activeTag: Object.keys(data.tag_options).sort()[0]
            //     });
            // });
        }
    }, {
        key: 'loadSiteConfig',
        value: function loadSiteConfig(siteName, cb) {
            // TODO: use this
        }
    }, {
        key: 'componentWillUpdate',
        value: function componentWillUpdate(nextProps, nextState) {
            if (this.state.info != null) {
                // console.log(this.state.info.sections.length);
                // console.log(nextState.info.sections.length);
                if (this.state.activeTag == nextState.activeTag) {
                    console.log("Saving");

                    var payload = new FormData();
                    payload.append('allowed_classifications', this.state.setClassifications);
                    payload.append('allowed_countries', this.state.setCountries);

                    // var data = new FormData();
                    // data.append( "json", JSON.stringify( payload ) );

                    fetch("/api/settings/save", {
                        method: "POST",
                        body: JSON.stringify(nextState.info),
                        headers: {
                            'user-agent': 'Mozilla/4.0 MDN Example',
                            'content-type': 'application/json; charset=utf-8'
                        }
                    }).then(function (res) {
                        return res.json();
                    }).then(function (data) {
                        console.log("Saved");
                    }).catch(function (err) {
                        console.log("Error", err);
                    });

                    // $.ajax({
                    //     type: "POST",
                    //     url: "/api/settings/save",
                    //     // The key needs to match your method's input parameter (case-sensitive).
                    //     data: JSON.stringify(nextState.info),
                    //     contentType: "application/json; charset=utf-8",
                    //     dataType: "json",
                    //     success: function(data){
                    //         console.log("Saved");
                    //     },
                    //     failure: function(errMsg) {
                    //         alert(errMsg);
                    //     }
                    // });
                }
            }
        }
    }, {
        key: 'onChangeSetting',
        value: function onChangeSetting(key, event) {
            var newState = JSON.parse(JSON.stringify(this.state.info));

            if (_.has(newState, key)) {}

            switch (key) {

                case 'require_pretime':
                case 'always_add_affils':
                case 'dataprovider_needs_approval':
                case 'refresh_ended_shows':
                case 'cleanup_old_pre':
                case 'approved_straight_to_cbftp':
                    var isTrue = event.target.value == 'true';
                    _.set(newState, key, isTrue);
                    break;

                case 'banned_groups':
                    _.set(newState, key, event.target.value.toUpperCase().split(" "));
                    break;

                case 'default_skiplists':
                case 'tags':
                case 'ignore_tags':
                    _.set(newState, key, event.target.value.split(" "));
                    break;

                case 'children_networks':
                    _.set(newState, key, event.target.value.split(","));
                    break;

                default:
                    console.log(key, event.target.value);
                    _.set(newState, key, event.target.value);
            }

            this.setState({
                info: newState
            });
        }
    }, {
        key: 'onChangeTag',
        value: function onChangeTag(newTag, e) {
            this.setState({
                activeTag: newTag
            });
            e.preventDefault();
            return false;
        }
    }, {
        key: 'onDeleteTag',
        value: function onDeleteTag() {}
    }, {
        key: 'addTag',
        value: function addTag() {
            var tag = prompt('Enter a tag name');
            if (tag != null && tag.length) {
                if (Object.keys(this.state.info.tag_options).map(Function.prototype.call, String.prototype.toLowerCase).includes(tag.toLowerCase())) {
                    alert("This tag already exists");
                    return;
                }

                var newState = JSON.parse(JSON.stringify(this.state.info));
                newState.tag_options[tag] = {
                    data_sources: [],
                    allowed_groups: [],
                    tag_requires: [],
                    tag_skiplist: []
                };
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: 'onChangeTagOptions',
        value: function onChangeTagOptions(key, event) {

            var newState = JSON.parse(JSON.stringify(this.state.info));

            var lastBit = key.split(".").slice(-1).pop();

            switch (lastBit) {
                case 'data_sources':
                    _.set(newState, key, event.target.value.split(" "));
                    break;
                case 'allowed_groups':
                    _.set(newState, key, event.target.value.split(" ").toUpperCase());
                    break;

                case 'tag_requires':
                case 'tag_skiplist':
                    _.set(newState, key, event.target.value.split("\n"));
                    break;

                default:
                //  console.log(key, event.target.value);
                //_.set(newState, key, event.target.value);
            }

            //console.log(newState);return;

            this.setState({
                info: newState
            });
        }
    }, {
        key: 'onChangeSchedule',
        value: function onChangeSchedule(dayIndex, dayIndexKey, e) {
            var newState = JSON.parse(JSON.stringify(this.state.info));

            _.set(newState.schedule, dayIndex + ".[" + dayIndexKey + "]", e.target.value);
            console.log(newState);

            this.setState({
                info: newState
            });
        }
    }, {
        key: 'render',
        value: function render() {
            var that = this;

            if (this.state.info === null) {
                return React.createElement(
                    'div',
                    { id: 'site-edit' },
                    'Loading...'
                );
            }

            var tagList = Object.keys(this.state.info.tag_options).sortIgnoreCase().map(function (key) {

                var tag = that.state.info.tag_options[key];

                var classes = classNames({
                    active: that.state.activeTag == key
                });

                return React.createElement(
                    'li',
                    { key: key, className: classes },
                    React.createElement(
                        'a',
                        { href: '#', onClick: that.onChangeTag.bind(that, key) },
                        key,
                        ' ',
                        React.createElement('i', { onClick: that.onDeleteTag.bind(that, key), className: 'fa fa-times', style: { float: "right" } })
                    )
                );
            });

            var tagDOM = null;
            if (this.state.activeTag != null) {

                var tagInfo = this.state.info.tag_options[this.state.activeTag];

                if (tagInfo != null) {
                    tagDOM = React.createElement(InputTagOption, {
                        key: this.state.activeTag,
                        tag: this.state.activeTag,
                        info: tagInfo,
                        onChange: this.onChangeTagOptions.bind(this)
                    });
                }
            }

            if (!this.state.info.children_networks) {
                this.state.info.children_networks = [];
            }

            var styles = {
                width: "60px"
            };

            var spacer = {
                display: "inline-block",
                width: "80px"
            };

            var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"].map(function (row, idx) {
                return React.createElement(
                    'tr',
                    { key: row },
                    React.createElement(
                        'td',
                        null,
                        row
                    ),
                    React.createElement(
                        'td',
                        { style: { width: "200px" } },
                        React.createElement('input', {
                            onChange: that.onChangeSchedule.bind(that, idx + 1, 0),
                            type: 'text',
                            className: 'form-control input-sm',
                            style: { width: "70px", marginRight: 0, display: "inline-block" },
                            defaultValue: that.state.info.schedule[idx + 1][0]
                        }),
                        ' -',
                        React.createElement('input', {
                            onChange: that.onChangeSchedule.bind(that, idx + 1, 1),
                            type: 'text',
                            className: 'form-control input-sm',
                            style: { width: "70px", display: "inline-block" },
                            defaultValue: that.state.info.schedule[idx + 1][1]
                        })
                    )
                );
            });

            return React.createElement(
                'div',
                null,
                React.createElement(
                    'h2',
                    null,
                    'Edit settings'
                ),
                React.createElement(
                    'div',
                    { className: 'form-horizontal' },
                    React.createElement(
                        'h3',
                        null,
                        'Basic settings'
                    ),
                    React.createElement(InputBooleanChoice, {
                        name: 'require_pretime',
                        label: 'Require pretime?',
                        description: 'Bla bla',
                        defaultValue: this.state.info.require_pretime,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputBooleanChoice, {
                        name: 'always_add_affils',
                        label: 'Always add affil sites to races (where possible)?',
                        description: 'Bla bla',
                        defaultValue: this.state.info.always_add_affils,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputText, {
                        name: 'data_exchange_channel',
                        label: 'Data exchange channel',
                        description: 'Bla bla',
                        defaultValue: this.state.info.data_exchange_channel,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputBooleanChoice, {
                        name: 'dataprovider_needs_approval',
                        label: 'Require approval for all data provider lookups? (NOT WORKING YET)',
                        description: 'Bla bla',
                        defaultValue: this.state.info.dataprovider_needs_approval,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputBooleanChoice, {
                        name: 'refresh_ended_shows',
                        label: 'Refresh data cache for \'Ended\' shows?',
                        description: 'Bla bla',
                        defaultValue: this.state.info.refresh_ended_shows,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputBooleanChoice, {
                        name: 'cleanup_old_pre',
                        label: 'Delete pre data older than 30 days?',
                        description: 'Bla bla',
                        defaultValue: this.state.info.cleanup_old_pre,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputText, {
                        name: 'default_skiplists',
                        label: 'Default skiplists (space-seperator)',
                        description: 'Bla bla',
                        defaultValue: this.state.info.default_skiplists.sort().join(" "),
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(
                        'h3',
                        null,
                        'Bad shit'
                    ),
                    React.createElement(InputText, {
                        name: 'baddir',
                        label: 'Regex to skip bad release names',
                        description: 'Bla bla',
                        defaultValue: this.state.info.baddir,
                        onChange: this.onChangeSetting.bind(this),
                        classes: 'code'
                    }),
                    React.createElement(InputText, {
                        name: 'banned_groups',
                        label: 'List of banned groups (space-seperator)',
                        description: 'Bla bla',
                        defaultValue: this.state.info.banned_groups.sort().join(" "),
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(
                        'h3',
                        null,
                        'Tags (bookmarks)'
                    ),
                    React.createElement(InputText, {
                        name: 'tags',
                        label: 'Tags (space-seperator)',
                        description: 'Bla bla',
                        defaultValue: this.state.info.tags.sort().join(" "),
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputText, {
                        name: 'ignore_tags',
                        label: 'Tags to ignore (space-seperator)',
                        description: 'Bla bla',
                        defaultValue: this.state.info.ignore_tags.sort().join(" "),
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(
                        'h3',
                        null,
                        'Tag options'
                    ),
                    React.createElement(
                        'div',
                        { className: 'row' },
                        React.createElement(
                            'div',
                            { className: 'col-md-3' },
                            React.createElement(
                                'ul',
                                { className: 'nav nav-pills nav-stacked', style: { marginBottom: "1em" } },
                                tagList
                            ),
                            React.createElement(
                                'a',
                                { className: 'btn btn-block btn-default', onClick: this.addTag.bind(this) },
                                'Add tag'
                            )
                        ),
                        React.createElement(
                            'div',
                            { className: 'col-md-9' },
                            tagDOM
                        )
                    ),
                    React.createElement(
                        'h3',
                        null,
                        'Misc. options'
                    ),
                    React.createElement(InputText, {
                        name: 'children_networks',
                        label: 'List of children\'s networks (comma-seperator)',
                        description: 'CBBC,Cartoon Network',
                        defaultValue: this.state.info.children_networks.sort().join(","),
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(
                        'h3',
                        null,
                        'cbftp'
                    ),
                    React.createElement(InputText, {
                        name: 'cbftp_host',
                        label: 'Hostname',
                        description: 'Hostname',
                        defaultValue: this.state.info.cbftp_host,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputText, {
                        name: 'cbftp_port',
                        label: 'Port',
                        description: 'Port',
                        defaultValue: this.state.info.cbftp_port,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputText, {
                        name: 'cbftp_password',
                        label: 'UDP password',
                        description: '',
                        defaultValue: this.state.info.cbftp_password,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(InputBooleanChoice, {
                        name: 'approved_straight_to_cbftp',
                        label: 'Send approved races straight to cbftp?',
                        description: 'Bla bla',
                        defaultValue: this.state.info.approved_straight_to_cbftp,
                        onChange: this.onChangeSetting.bind(this)
                    }),
                    React.createElement(
                        'h3',
                        null,
                        'Scheduling'
                    ),
                    React.createElement(
                        'p',
                        null,
                        'If you enable cbftp integration, you can limit its schedule below so UDP commands are only sent between certain hours.'
                    ),
                    React.createElement(
                        'table',
                        { className: 'table table-bordered', style: { width: "auto" } },
                        React.createElement(
                            'tbody',
                            null,
                            days
                        )
                    )
                )
            );
        }
    }]);

    return SettingsEditor;
}(React.Component);
//# sourceMappingURL=SettingsEditor.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SiteEdit = function (_React$Component) {
    _inherits(SiteEdit, _React$Component);

    function SiteEdit(props) {
        _classCallCheck(this, SiteEdit);

        var _this = _possibleConstructorReturn(this, (SiteEdit.__proto__ || Object.getPrototypeOf(SiteEdit)).call(this, props));

        _this.state = {
            info: null,
            activeSection: null
        };
        return _this;
    }

    _createClass(SiteEdit, [{
        key: "componentWillMount",
        value: function componentWillMount() {
            var that = this;

            fetch("/api/site/" + this.props.name, { method: "get" }).then(function (response) {
                return response.json().then(function (data) {

                    var activeSection = null;
                    if (data.sections.length > 0) {
                        activeSection = _.sortBy(data.sections, "name")[0].name;
                    }

                    that.setState({
                        info: data,
                        activeSection: activeSection
                    });
                });
            }).catch(function (err) {
                // Error :(
            });
        }
    }, {
        key: "loadSiteConfig",
        value: function loadSiteConfig(siteName, cb) {
            // TODO: use this
        }
    }, {
        key: "componentWillUpdate",
        value: function componentWillUpdate(nextProps, nextState) {
            if (this.state.info != null) {
                // console.log(this.state.info.sections.length);
                // console.log(nextState.info.sections.length);
                if (this.state.activeSection == nextState.activeSection || this.state.info.sections.length != nextState.info.sections.length) {
                    console.log("Saving");

                    fetch("/api/site/" + this.props.name + "/save", {
                        method: "POST",
                        body: JSON.stringify(nextState.info),
                        headers: new Headers({
                            'Content-Type': 'application/json'
                        })
                    }).then(function (res) {
                        return res.json();
                    }).then(function (data) {
                        console.log("Saved");
                    }).catch(function (err) {
                        console.log("Error saving", err);
                    });
                }
            }
        }
    }, {
        key: "addSection",
        value: function addSection() {
            var newSection = prompt("Enter section (directory) name:");
            if (newSection !== null && newSection.length) {

                var found = false;
                _.forOwn(this.state.info.sections, function (value, key) {
                    if (value.name.toLowerCase() == newSection.toLowerCase()) {
                        found = true;
                    }
                });

                if (found) {
                    alert("Section already exists");
                    return;
                }

                var newState = JSON.parse(JSON.stringify(this.state.info));
                newState.sections.push({
                    name: newSection,
                    bnc: null,
                    pretime: 60,
                    tags: [],
                    skiplists: [],
                    rules: [],
                    dupeRules: {
                        "source.firstWins": false,
                        "source.priority": ""
                    }
                });
                this.setState({
                    info: newState,
                    activeSection: newSection
                });
            }
        }
    }, {
        key: "onDeleteSection",
        value: function onDeleteSection(section, event) {
            if (confirm("Are you sure you want to permanently remove this section: " + section + "?")) {
                var newState = JSON.parse(JSON.stringify(this.state.info));

                var sectionKey = _.findKey(newState.sections, ["name", section]);

                if (sectionKey >= 0) {
                    newState.sections.splice(sectionKey, 1);

                    this.setState({
                        info: newState
                    });

                    if (section == this.state.activeSection) {
                        this.setState({
                            activeSection: _.sortBy(this.state.info.sections, "name")[0].name
                        });
                    }
                }
            }
            event.stopPropagation();
            event.preventDefault();
        }
    }, {
        key: "onChangeSection",
        value: function onChangeSection(newSection, e) {
            this.setState({
                activeSection: newSection
            });
            e.preventDefault();
            return false;
        }
    }, {
        key: "onChangeConfig",
        value: function onChangeConfig(key, event) {
            var newState = JSON.parse(JSON.stringify(this.state.info));

            if (_.has(newState, key)) {}

            switch (key) {
                case "enabled":
                case "irc.strings.newstring-isregex":
                case "irc.strings.endstring-isregex":
                case "irc.strings.prestring-isregex":
                    _.set(newState, key, event.target.checked);
                    break;

                case "affils":
                case "banned_groups":
                    var val = event.target.value.toUpperCase();
                    _.set(newState, key, val.split(" "));
                    break;

                default:
                    _.set(newState, key, event.target.value);
            }

            this.setState({
                info: newState
            });
        }
    }, {
        key: "sortAffils",
        value: function sortAffils() {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            _.set(newState, "affils", _.uniq(this.state.info.affils.sort()));
            this.setState({
                info: newState
            });
        }
    }, {
        key: "sortBannedGroups",
        value: function sortBannedGroups() {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            _.set(newState, "banned_groups", _.uniq(this.state.info.banned_groups.sort()));
            this.setState({
                info: newState
            });
        }
    }, {
        key: "onChangeSectionConfig",
        value: function onChangeSectionConfig(section, key, event) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", section]);
            if (sectionKey >= 0) {
                var val = event.target.value;
                if (key == "rules") {
                    val = event.target.value.split("\n");
                }

                newState.sections[sectionKey][key] = val;
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onChangeSectionConfigRules",
        value: function onChangeSectionConfigRules(value) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
            if (sectionKey >= 0) {
                value = _.without(value.trim().split("\n"), "");
                newState.sections[sectionKey]["rules"] = value;
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onChangeSectionDupeRules",
        value: function onChangeSectionDupeRules(k, event) {

            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
            if (sectionKey >= 0) {

                switch (k) {
                    case 'source.firstWins':
                        newState.sections[sectionKey]["dupeRules"][k] = event.target.checked;
                        break;
                    case 'source.priority':
                        newState.sections[sectionKey]["dupeRules"][k] = event.target.value;
                        break;
                }

                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onAddTag",
        value: function onAddTag(tag) {
            var trigger = prompt("Choose a regex trigger", "/.*/i");
            if (trigger.length) {
                var newState = JSON.parse(JSON.stringify(this.state.info));
                var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
                newState.sections[sectionKey].tags.push({
                    tag: tag,
                    trigger: trigger,
                    rules: []
                });
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onRemoveTag",
        value: function onRemoveTag(tag) {
            if (confirm("Are you sure you want to remove this tag?")) {
                var newState = JSON.parse(JSON.stringify(this.state.info));
                var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
                var tagKey = _.findKey(newState.sections[sectionKey].tags, ["tag", tag]);
                newState.sections[sectionKey].tags.splice(tagKey, 1);
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onChangeTag",
        value: function onChangeTag(tag, newValues) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
            var tagKey = _.findKey(newState.sections[sectionKey].tags, ["tag", tag]);

            for (var k in newValues) {
                if (newValues.hasOwnProperty(k)) {
                    var value = newValues[k];
                    if (k == "rules") {
                        value = _.without(value.trim().split("\n"), "");
                    }

                    newState.sections[sectionKey].tags[tagKey][k] = value;
                }
            }

            //newState.sections[sectionKey].tags[tagKey].trigger = trigger;
            //newState.sections[sectionKey].tags[tagKey].rules = rules.split('\n');
            this.setState({
                info: newState
            });
        }
    }, {
        key: "onAddSkiplist",
        value: function onAddSkiplist(skiplist) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
            if (this.state.info.sections[sectionKey].skiplists.indexOf(skiplist) === -1) {
                newState.sections[sectionKey].skiplists.push(skiplist);
                this.setState({
                    info: newState
                });
            }
        }
    }, {
        key: "onRemoveSkiplist",
        value: function onRemoveSkiplist(skiplist) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, ["name", this.state.activeSection]);
            var skiplistKey = this.state.info.sections[sectionKey].skiplists.indexOf(skiplist);
            newState.sections[sectionKey].skiplists.splice(skiplistKey, 1);
            this.setState({
                info: newState
            });
        }
    }, {
        key: "testAnnounceString",
        value: function testAnnounceString(key) {
            var info = this.state.info;
            var str = _.get(info, key);

            if (str === null || !str.length) {
                return;
            }

            var section = _.get(info, key + "-section");
            var rlsname = _.get(info, key + "-rls");
            if (!section || !rlsname || !section.length || !rlsname.length) {
                return;
            }

            var testString = prompt("Enter a sample string...");
            if (!testString.length) {
                return;
            }

            fetch("/api/site/" + this.props.name + "/testString", {
                method: "POST",
                body: JSON.stringify({
                    testString: testString,
                    key: key
                }),
                headers: new Headers({
                    'Content-Type': 'application/json'
                })
            }).then(function (res) {
                return res.json();
            }).then(function (data) {
                if (data.matched) {
                    alert("Matched section: " + data.section + " and rlsname: " + data.rlsname);
                } else {
                    alert("No match");
                }
            }).catch(function (err) {
                console.log("Error saving", err);
            });
        }
    }, {
        key: "render",
        value: function render() {
            var that = this;

            if (this.state.info === null) {
                return React.createElement(
                    "div",
                    { id: "site-edit" },
                    "Loading..."
                );
            }

            var sectionList = _.sortBy(this.state.info.sections, "name").map(function (section) {
                var classes = classNames({
                    active: that.state.activeSection == section.name,
                    notags: section.tags.length == 0 || typeof section.rules == "undefined" || section.rules.length == 0 && section.tags.length == 0
                });

                return React.createElement(
                    "li",
                    { key: section.name, className: classes },
                    React.createElement(
                        "a",
                        {
                            href: "#",
                            onClick: that.onChangeSection.bind(that, section.name)
                        },
                        section.name,
                        " ",
                        "(",
                        section.tags.length,
                        ")",
                        " ",
                        React.createElement("i", {
                            onClick: that.onDeleteSection.bind(that, section.name),
                            className: "icon-times",
                            style: { float: "right" }
                        })
                    )
                );
            });

            var sectionDOM = null;
            if (this.state.activeSection != null) {
                var section = null;
                for (var i = 0; i < this.state.info.sections.length; i++) {
                    if (this.state.info.sections[i].name == this.state.activeSection) {
                        section = this.state.info.sections[i];
                    }
                }

                if (section != null) {
                    sectionDOM = React.createElement(SiteEditSection, {
                        info: section,
                        onChange: this.onChangeSectionConfig.bind(this),
                        onAddTag: this.onAddTag.bind(this),
                        onRemoveTag: this.onRemoveTag.bind(this),
                        onChangeTag: this.onChangeTag.bind(this),
                        onAddSkiplist: this.onAddSkiplist.bind(this),
                        onRemoveSkiplist: this.onRemoveSkiplist.bind(this),
                        onChangeSectionRules: this.onChangeSectionConfigRules.bind(this),
                        onChangeSectionDupeRules: this.onChangeSectionDupeRules.bind(this)
                    });
                }
            }

            return React.createElement(
                "div",
                { id: "site-edit" },
                React.createElement(
                    "h1",
                    null,
                    "Editing site: ",
                    this.props.name
                ),
                React.createElement(
                    "fieldset",
                    null,
                    React.createElement(
                        "legend",
                        null,
                        "Basic Information",
                        " ",
                        React.createElement(
                            "a",
                            { "data-help": "sites", className: "pull-right" },
                            React.createElement("i", { className: "icon-question-circle" })
                        )
                    ),
                    React.createElement(
                        "div",
                        { className: "checkbox" },
                        React.createElement(
                            "label",
                            null,
                            React.createElement("input", {
                                tabIndex: "1",
                                id: "site-enabled",
                                type: "checkbox",
                                value: "1",
                                defaultChecked: this.state.info.enabled,
                                onChange: this.onChangeConfig.bind(this, "enabled")
                            }),
                            "Enabled?"
                        )
                    )
                ),
                React.createElement(
                    "fieldset",
                    null,
                    React.createElement(
                        "legend",
                        null,
                        "IRC Configuration"
                    ),
                    React.createElement(
                        "div",
                        { className: "form-horizontal" },
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "Channel"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-4" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Regex to match channel",
                                    value: this.state.info.irc.channel,
                                    onChange: this.onChangeConfig.bind(this, "irc.channel")
                                })
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "Bot"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-4" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Regex to match bot",
                                    value: this.state.info.irc.bot,
                                    onChange: this.onChangeConfig.bind(this, "irc.bot")
                                })
                            )
                        )
                    )
                ),
                React.createElement(
                    "fieldset",
                    null,
                    React.createElement(
                        "legend",
                        null,
                        "Announce strings"
                    ),
                    React.createElement(
                        "div",
                        { className: "form-horizontal" },
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement("div", { className: "col-sm-8" }),
                            React.createElement(
                                "div",
                                { className: "col-sm-1" },
                                React.createElement(
                                    "strong",
                                    null,
                                    "Section"
                                )
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-1" },
                                React.createElement(
                                    "strong",
                                    null,
                                    "Rlsname"
                                )
                            ),
                            React.createElement("div", { className: "col-md-2" })
                        ),
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "New string"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-6" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "e.g. New in &section by &user with &release",
                                    value: this.state.info.irc.strings.newstring,
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.newstring")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Section",
                                    value: this.state.info.irc.strings["newstring-section"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.newstring-section")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Rls",
                                    value: this.state.info.irc.strings["newstring-rls"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.newstring-rls")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "div",
                                    { className: "checkbox" },
                                    React.createElement(
                                        "label",
                                        null,
                                        React.createElement("input", {
                                            type: "checkbox",
                                            value: "",
                                            defaultChecked: this.state.info.irc.strings["newstring-isregex"],
                                            onChange: this.onChangeConfig.bind(this, "irc.strings.newstring-isregex")
                                        }),
                                        "Regex?"
                                    )
                                )
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "a",
                                    {
                                        className: "btn btn-default btn-block",
                                        onClick: this.testAnnounceString.bind(this, "irc.strings.newstring")
                                    },
                                    "Test"
                                )
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "End string"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-6" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "e.g. End in &section by &user with &release",
                                    value: this.state.info.irc.strings["endstring"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.endstring")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Tok #",
                                    value: this.state.info.irc.strings["endstring-section"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.endstring-section")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Tok #",
                                    value: this.state.info.irc.strings["endstring-rls"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.endstring-rls")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "div",
                                    { className: "checkbox" },
                                    React.createElement(
                                        "label",
                                        null,
                                        React.createElement("input", {
                                            type: "checkbox",
                                            value: "",
                                            defaultChecked: this.state.info.irc.strings["endstring-isregex"],
                                            onChange: this.onChangeConfig.bind(this, "irc.strings.endstring-isregex")
                                        }),
                                        "Regex?"
                                    )
                                )
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "a",
                                    {
                                        className: "btn btn-default btn-block",
                                        onClick: this.testAnnounceString.bind(this, "irc.strings.endstring")
                                    },
                                    "Test"
                                )
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "Pre string"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-6" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "e.g. Pre in &section by &user with &release",
                                    value: this.state.info.irc.strings["prestring"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.prestring")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Tok #",
                                    value: this.state.info.irc.strings["prestring-section"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.prestring-section")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "Tok #",
                                    value: this.state.info.irc.strings["prestring-rls"],
                                    onChange: this.onChangeConfig.bind(this, "irc.strings.prestring-rls")
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "div",
                                    { className: "checkbox" },
                                    React.createElement(
                                        "label",
                                        null,
                                        React.createElement("input", {
                                            type: "checkbox",
                                            value: "",
                                            defaultChecked: this.state.info.irc.strings["prestring-isregex"],
                                            onChange: this.onChangeConfig.bind(this, "irc.strings.prestring-isregex")
                                        }),
                                        "Regex?"
                                    )
                                )
                            ),
                            React.createElement(
                                "div",
                                { className: "col-md-1" },
                                React.createElement(
                                    "a",
                                    {
                                        className: "btn btn-default btn-block",
                                        onClick: this.testAnnounceString.bind(this, "irc.strings.prestring")
                                    },
                                    "Test"
                                )
                            )
                        )
                    )
                ),
                React.createElement(
                    "fieldset",
                    null,
                    React.createElement(
                        "legend",
                        null,
                        "Groups"
                    ),
                    React.createElement(
                        "div",
                        { className: "form-horizontal" },
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "Affils"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-9" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "List of affils (seperated by spaces)",
                                    value: this.state.info.affils.join(" "),
                                    onChange: this.onChangeConfig.bind(this, "affils"),
                                    style: { textTransform: "uppercase" }
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-1" },
                                React.createElement(
                                    "a",
                                    {
                                        className: "btn btn-default",
                                        onClick: this.sortAffils.bind(this)
                                    },
                                    "Sort A-Z"
                                )
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "form-group" },
                            React.createElement(
                                "label",
                                {
                                    htmlFor: "inputEmail3",
                                    className: "col-sm-2 control-label"
                                },
                                "Banned groups"
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-9" },
                                React.createElement("input", {
                                    type: "text",
                                    className: "form-control",
                                    id: "inputEmail3",
                                    placeholder: "List of banned groups (seperated by spaces)",
                                    value: this.state.info.banned_groups.join(" "),
                                    onChange: this.onChangeConfig.bind(this, "banned_groups"),
                                    style: { textTransform: "uppercase" }
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "col-sm-1" },
                                React.createElement(
                                    "a",
                                    {
                                        className: "btn btn-default",
                                        onClick: this.sortBannedGroups.bind(this)
                                    },
                                    "Sort A-Z"
                                )
                            )
                        )
                    )
                ),
                React.createElement(
                    "fieldset",
                    null,
                    React.createElement(
                        "legend",
                        null,
                        "Sections"
                    ),
                    React.createElement(
                        "div",
                        { className: "row" },
                        React.createElement(
                            "div",
                            { className: "col-md-3" },
                            React.createElement(
                                "ul",
                                {
                                    className: "nav nav-pills nav-stacked",
                                    style: { marginBottom: "1em" }
                                },
                                sectionList
                            ),
                            React.createElement(
                                "a",
                                {
                                    className: "btn btn-block btn-default",
                                    onClick: this.addSection.bind(this)
                                },
                                "Add section"
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "col-md-9" },
                            sectionDOM
                        )
                    )
                )
            );
        }
    }]);

    return SiteEdit;
}(React.Component);
//# sourceMappingURL=SiteEdit.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SiteEditSection = function (_React$Component) {
    _inherits(SiteEditSection, _React$Component);

    function SiteEditSection(props) {
        _classCallCheck(this, SiteEditSection);

        var _this = _possibleConstructorReturn(this, (SiteEditSection.__proto__ || Object.getPrototypeOf(SiteEditSection)).call(this, props));

        _this.state = {
            tags: [],
            skiplists: [],
            activeTab: "general",
            editingTag: null
        };

        return _this;
    }

    _createClass(SiteEditSection, [{
        key: "componentWillMount",
        value: function componentWillMount() {

            var that = this;

            fetch("/api/section", { method: "get" }).then(function (response) {
                return response.json().then(function (data) {
                    that.setState({
                        tags: data
                    });
                });
            }).catch(function (err) {
                // Error :(
            });

            fetch("/api/skiplist", { method: "get" }).then(function (response) {
                return response.json().then(function (data) {
                    that.setState({
                        skiplists: data
                    });
                });
            }).catch(function (err) {
                // Error :(
            });
        }
    }, {
        key: "componentWillReceiveProps",
        value: function componentWillReceiveProps(nextProps) {
            if (this.props.info.name != nextProps.info.name) {
                this.setState({
                    editingTag: null
                });
            }
        }
    }, {
        key: "onChangeTab",
        value: function onChangeTab(tab, event) {
            this.setState({
                activeTab: tab
            });
            event.preventDefault();
        }

        /* tag change stuff */

    }, {
        key: "onChooseTagToEdit",
        value: function onChooseTagToEdit(tag, event) {
            this.setState({
                editingTag: tag
            });
        }
    }, {
        key: "onToggleTag",
        value: function onToggleTag(tag, checked) {
            if (checked == false) {
                this.props.onRemoveTag(tag);
            } else {
                this.props.onAddTag(tag);
            }
        }
    }, {
        key: "onChangeTrigger",
        value: function onChangeTrigger(event) {
            this.props.onChangeTag(this.state.editingTag, { trigger: event.target.value });
        }
    }, {
        key: "onChangeTagRule",
        value: function onChangeTagRule(rules) {
            this.props.onChangeTag(this.state.editingTag, { rules: rules });
        }

        /* skiplist change stuff */

    }, {
        key: "onToggleSkiplist",
        value: function onToggleSkiplist(skiplist, checked) {
            if (checked == false) {
                this.props.onRemoveSkiplist(skiplist);
            } else {
                this.props.onAddSkiplist(skiplist);
            }
        }
    }, {
        key: "render",
        value: function render() {

            var that = this;

            var tagList = null;
            if (this.state.tags.length) {
                tagList = this.state.tags.map(function (tag) {

                    var tickOrCross = React.createElement("i", { className: "icon-times", style: { color: "#666" }, onClick: that.onToggleTag.bind(that, tag, true) });
                    var btn = null;
                    for (var i = 0; i < that.props.info.tags.length; i++) {
                        if (that.props.info.tags[i].tag == tag) {
                            tickOrCross = React.createElement("i", { className: "icon-check", style: { color: "#00E500" }, onClick: that.onToggleTag.bind(that, tag, false) });
                            btn = React.createElement(
                                "span",
                                null,
                                " - ",
                                React.createElement(
                                    "a",
                                    { onClick: that.onChooseTagToEdit.bind(that, tag) },
                                    "[Edit]"
                                )
                            );
                        }
                    }

                    return React.createElement(
                        "div",
                        { className: "col-md-4", key: tag },
                        tickOrCross,
                        " ",
                        tag,
                        " ",
                        btn
                    );
                });
            }

            var skiplistList = null;
            if (Object.keys(this.state.skiplists).length) {
                skiplistList = Object.keys(this.state.skiplists).map(function (skiplist) {

                    var tickOrCross = React.createElement("i", { className: "icon-times", style: { color: "#666" }, onClick: that.onToggleSkiplist.bind(that, skiplist, true) });
                    for (var i = 0; i < that.props.info.skiplists.length; i++) {
                        if (that.props.info.skiplists[i] == skiplist) {
                            tickOrCross = React.createElement("i", { className: "icon-check", style: { color: "#00E500" }, onClick: that.onToggleSkiplist.bind(that, skiplist, false) });
                        }
                    }

                    return React.createElement(
                        "div",
                        { className: "col-md-4", key: skiplist },
                        tickOrCross,
                        " ",
                        skiplist
                    );
                });
            }

            var triggerEditor = null;
            if (this.state.editingTag !== null) {
                var tagKey = _.findKey(this.props.info.tags, ['tag', this.state.editingTag]);
                var trigger = this.props.info.tags[tagKey].trigger;
                triggerEditor = React.createElement(
                    "div",
                    { style: { marginTop: "1em" } },
                    React.createElement(
                        "label",
                        { htmlFor: "exampleInputName2" },
                        "Trigger for ",
                        this.state.editingTag,
                        ":"
                    ),
                    React.createElement(
                        "div",
                        { className: "form-group" },
                        React.createElement("input", { type: "text", className: "form-control",
                            id: "exampleInputName2", placeholder: "trigger..", size: "6",
                            value: trigger,
                            onChange: this.onChangeTrigger.bind(this)
                        })
                    )
                );
            }

            var tagRuleEditor = null;
            if (this.state.editingTag !== null) {
                var tagKey = _.findKey(this.props.info.tags, ['tag', this.state.editingTag]);
                var rules = this.props.info.tags[tagKey].rules;
                if (typeof rules != 'undefined' && rules.length) {
                    rules = rules.join('\n');
                }
                tagRuleEditor = React.createElement(
                    "div",
                    { style: { marginTop: "1em" } },
                    React.createElement(
                        "label",
                        null,
                        "Rules for ",
                        this.state.editingTag,
                        ":"
                    ),
                    React.createElement(
                        "div",
                        { className: "form-group" },
                        React.createElement(RulesEditor, { defaultValue: rules, onChange: this.onChangeTagRule.bind(this), key: this.props.info.name + this.state.editingTag })
                    )
                );
            }

            var tabs = ["general", "tags", "rules", "dupes", "skiplists"];
            var tabClasses = [];
            var tabList = tabs.map(function (tab) {

                var label = tab.charAt(0).toUpperCase() + tab.slice(1);

                var badge = '';
                if (tab == "rules" && typeof that.props.info.rules != 'undefined') {
                    badge = React.createElement(
                        "span",
                        { className: "badge" },
                        that.props.info.rules.length
                    );
                } else if (tab == "tags" && typeof that.props.info.tags != 'undefined') {
                    badge = React.createElement(
                        "span",
                        { className: "badge" },
                        that.props.info.tags.length
                    );
                } else if (tab == "skiplists" && typeof that.props.info.skiplists != 'undefined') {
                    badge = React.createElement(
                        "span",
                        { className: "badge" },
                        that.props.info.skiplists.length
                    );
                }

                var classes = classNames({
                    active: that.state.activeTab == tab
                });

                tabClasses[tab] = classNames({
                    "tab-panel": true,
                    "tab-panel-visible": that.state.activeTab == tab
                });

                return React.createElement(
                    "li",
                    { className: classes, key: tab, role: "presentation", "data-toggle": "general" },
                    React.createElement(
                        "a",
                        { href: "#", onClick: that.onChangeTab.bind(that, tab) },
                        label,
                        " ",
                        badge
                    )
                );
            });

            var rules = "";

            if (typeof this.props.info.rules != 'undefined') {
                rules = this.props.info.rules.join("\n");
            }

            var dupeFirstWins = this.props.info.dupeRules["source.firstWins"];

            var dupeHelpText = null;
            if (this.props.info.dupeRules["source.priority"] && this.props.info.dupeRules["source.priority"].length) {
                var dupeParts = this.props.info.dupeRules["source.priority"].split(",");
                if (dupeParts.length >= 2) {
                    dupeHelpText = "Firstly " + dupeParts[0] + " is allowed, followed by " + dupeParts.slice(1).join(", ");
                }
            }

            return React.createElement(
                "div",
                null,
                React.createElement(
                    "ul",
                    { className: "nav nav-tabs", id: "section-tabs" },
                    tabList
                ),
                React.createElement(
                    "div",
                    { className: "well well-tag" },
                    React.createElement(
                        "div",
                        { id: "general", className: tabClasses['general'] },
                        React.createElement(
                            "div",
                            { className: "form-inline" },
                            React.createElement(
                                "div",
                                { className: "form-group" },
                                React.createElement(
                                    "label",
                                    { htmlFor: "exampleInputName2" },
                                    "Pretime (min.)"
                                ),
                                React.createElement("input", { type: "text", className: "form-control",
                                    id: "exampleInputName2", placeholder: "300", size: "6",
                                    onChange: this.props.onChange.bind(this, this.props.info.name, "pretime"), value: this.props.info.pretime
                                })
                            ),
                            React.createElement(
                                "div",
                                { className: "form-group" },
                                React.createElement(
                                    "label",
                                    { htmlFor: "exampleInputEmail2" },
                                    "BNC"
                                ),
                                React.createElement("input", { type: "type", className: "form-control",
                                    id: "exampleInputEmail2", placeholder: "",
                                    onChange: this.props.onChange.bind(this, this.props.info.name, "bnc"), value: this.props.info.bnc
                                })
                            )
                        )
                    ),
                    React.createElement(
                        "div",
                        { id: "tags", className: tabClasses['tags'] },
                        React.createElement(
                            "div",
                            { className: "" },
                            React.createElement(
                                "label",
                                { htmlFor: "exampleInputName2" },
                                "Choose tags:"
                            ),
                            React.createElement(
                                "div",
                                { className: "row" },
                                tagList
                            ),
                            triggerEditor,
                            tagRuleEditor
                        )
                    ),
                    React.createElement(
                        "div",
                        { id: "rules", className: tabClasses['rules'] },
                        React.createElement(
                            "div",
                            { className: "" },
                            React.createElement(
                                "label",
                                { htmlFor: "exampleInputName2", style: { display: 'block' } },
                                "Rules"
                            ),
                            React.createElement(RulesEditor, { key: this.props.info.name, defaultValue: rules, onChange: this.props.onChangeSectionRules.bind(this) })
                        )
                    ),
                    React.createElement(
                        "div",
                        { id: "dupes", className: tabClasses['dupes'] },
                        React.createElement(
                            "div",
                            { className: "" },
                            React.createElement(
                                "label",
                                { htmlFor: "exampleInputName2", style: { display: 'block' } },
                                "Source rules (only use on TV sections):"
                            ),
                            React.createElement(
                                "div",
                                { className: "form-group" },
                                React.createElement(
                                    "div",
                                    { className: "checkbox" },
                                    React.createElement(
                                        "label",
                                        null,
                                        React.createElement("input", { type: "checkbox", checked: this.props.info.dupeRules["source.firstWins"], defaultChecked: this.props.info.dupeRules["source.firstWins"], onClick: this.props.onChangeSectionDupeRules.bind(this, "source.firstWins") }),
                                        " First on site wins?"
                                    )
                                )
                            ),
                            React.createElement(
                                "div",
                                { className: "form-group" },
                                React.createElement(
                                    "label",
                                    { htmlFor: "exampleInputEmail2" },
                                    "Priority:"
                                ),
                                React.createElement("input", { type: "type", className: "form-control",
                                    id: "exampleInputEmail2", placeholder: "",
                                    onChange: this.props.onChangeSectionDupeRules.bind(this, "source.priority"), value: this.props.info.dupeRules["source.priority"]
                                }),
                                this.props.info.dupeRules["source.priority"] && React.createElement(
                                    "span",
                                    { className: "help-block" },
                                    dupeHelpText,
                                    ">"
                                )
                            )
                        )
                    ),
                    React.createElement(
                        "div",
                        { id: "skiplists", className: tabClasses['skiplists'] },
                        React.createElement(
                            "div",
                            { className: "" },
                            React.createElement(
                                "label",
                                { htmlFor: "" },
                                "Choose skiplists:"
                            ),
                            React.createElement(
                                "div",
                                { className: "row" },
                                skiplistList
                            )
                        )
                    )
                )
            );
        }
    }]);

    return SiteEditSection;
}(React.Component);
//# sourceMappingURL=SiteEditSection.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SkiplistEdit = function (_React$Component) {
    _inherits(SkiplistEdit, _React$Component);

    function SkiplistEdit(props) {
        _classCallCheck(this, SkiplistEdit);

        var _this = _possibleConstructorReturn(this, (SkiplistEdit.__proto__ || Object.getPrototypeOf(SkiplistEdit)).call(this, props));

        _this.state = {
            skiplists: {}
        };
        return _this;
    }

    _createClass(SkiplistEdit, [{
        key: "componentDidMount",
        value: function componentDidMount() {
            var that = this;
            fetch("/api/skiplist/", { method: "get" }).then(function (response) {
                return response.json().then(function (json) {
                    that.setState({
                        skiplists: json,
                        loaded: true
                    });
                });
            }).catch(function (err) {
                // Error :(
            });
        }
    }, {
        key: "componentWillUpdate",
        value: function componentWillUpdate(nextProps, nextState) {
            if (this.state.skiplists != null) {
                console.log("Saving");

                fetch("/api/skiplist/save", {
                    method: "POST",
                    body: JSON.stringify(nextState.skiplists),
                    headers: new Headers({
                        'Content-Type': 'application/json'
                    })
                }).then(function (res) {
                    return res.json();
                }).then(function (data) {
                    console.log("Saved");
                }).catch(function (err) {
                    console.log("Error saving", err);
                });
            }
        }
    }, {
        key: "componentDidUpdate",
        value: function componentDidUpdate(prevProps, prevState) {
            if (prevState.loaded === false) {
                return;
            }
        }
    }, {
        key: "_checkItemDoesntExist",
        value: function _checkItemDoesntExist(name) {}
    }, {
        key: "addNormal",
        value: function addNormal() {
            var name = prompt("Enter a unique name for this item skiplist (a-z0-9_)");
            if (name.length) {
                if (_.has(this.state.skiplists, name)) {
                    return alert("An item with this name already exists");
                }

                var newObj = this.state.skiplists;
                newObj[name] = {
                    items: [],
                    shared: true
                };
                this.setState({
                    skiplists: newObj
                });
            } else {
                alert("You didn't enter anything");
            }
        }
    }, {
        key: "addRegex",
        value: function addRegex() {
            var name = prompt("Enter a unique name for this regex skiplist (a-z0-9_)");
            if (name.length) {
                if (_.has(this.state.skiplists, name)) {
                    return alert("An item with this name already exists");
                }

                var newObj = this.state.skiplists;
                newObj[name] = {
                    regex: "",
                    shared: true
                };
                this.setState({
                    skiplists: newObj
                });
            } else {
                alert("You didn't enter anything");
            }
        }
    }, {
        key: "changeItem",
        value: function changeItem(type, name, event) {

            var newState = JSON.parse(JSON.stringify(this.state.skiplists));

            var obj = {
                shared: true
            };
            if (type == "regex") {
                obj.regex = event.target.value;
            } else {
                obj.items = event.target.value.split("\n");
            }

            _.set(newState, name, obj);

            this.setState({
                skiplists: newState
            });
        }
    }, {
        key: "deleteItem",
        value: function deleteItem(name, event) {
            if (confirm('Are you sure you want to delete the skiplist "' + name + '"?')) {
                var newState = JSON.parse(JSON.stringify(this.state.skiplists));
                delete newState[name];
                this.setState({
                    skiplists: newState
                });
            }
        }
    }, {
        key: "render",
        value: function render() {
            var that = this;

            console.log(this.state);

            var skiplistList = null;
            if (Object.keys(this.state.skiplists).length) {
                skiplistList = Object.keys(this.state.skiplists).map(function (skiplist) {
                    var editor = null;
                    var isRegex = null;
                    if (typeof that.state.skiplists[skiplist].items !== "undefined") {
                        var editor = React.createElement(SkiplistItems, {
                            name: skiplist,
                            items: that.state.skiplists[skiplist].items,
                            onChange: that.changeItem.bind(that)
                        });
                    } else {
                        var editor = React.createElement(SkiplistItemRegex, {
                            name: skiplist,
                            regex: that.state.skiplists[skiplist].regex,
                            onChange: that.changeItem.bind(that)
                        });
                        isRegex = React.createElement(
                            "span",
                            null,
                            " (regex)"
                        );
                    }

                    return React.createElement(
                        "div",
                        { className: "row", key: skiplist },
                        React.createElement(
                            "div",
                            { className: "col-md-3" },
                            React.createElement("i", { className: "icon-trash-o", onClick: that.deleteItem.bind(that, skiplist) }),
                            " ",
                            React.createElement(
                                "strong",
                                null,
                                skiplist,
                                " ",
                                isRegex
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "col-md-9" },
                            editor,
                            React.createElement("br", null)
                        )
                    );
                });
            }

            return React.createElement(
                "div",
                { className: "well" },
                React.createElement(
                    "h3",
                    null,
                    "Skiplists ",
                    React.createElement(
                        "a",
                        { className: "pull-right", "data-help": "skiplists" },
                        React.createElement("i", { className: "icon-question-circle" })
                    )
                ),
                React.createElement(
                    "p",
                    null,
                    React.createElement(
                        "a",
                        {
                            className: "btn btn-primary",
                            onClick: this.addRegex.bind(this)
                        },
                        "Add regex item"
                    ),
                    " ",
                    React.createElement(
                        "a",
                        {
                            className: "btn btn-primary",
                            onClick: this.addNormal.bind(this)
                        },
                        "Add wildcard item"
                    )
                ),
                skiplistList
            );
        }
    }]);

    return SkiplistEdit;
}(React.Component);
//# sourceMappingURL=SkiplistEdit.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SkiplistItemRegex = function (_React$Component) {
    _inherits(SkiplistItemRegex, _React$Component);

    function SkiplistItemRegex(props) {
        _classCallCheck(this, SkiplistItemRegex);

        var _this = _possibleConstructorReturn(this, (SkiplistItemRegex.__proto__ || Object.getPrototypeOf(SkiplistItemRegex)).call(this, props));

        _this.state = {};
        return _this;
    }

    _createClass(SkiplistItemRegex, [{
        key: "render",
        value: function render() {
            var that = this;

            return React.createElement("textarea", { type: "text", className: "form-control", rows: 10, defaultValue: this.props.regex, onChange: this.props.onChange.bind(this, "regex", this.props.name) });
        }
    }]);

    return SkiplistItemRegex;
}(React.Component);
//# sourceMappingURL=SkiplistItemRegex.js.map

"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SkiplistItems = function (_React$Component) {
    _inherits(SkiplistItems, _React$Component);

    function SkiplistItems(props) {
        _classCallCheck(this, SkiplistItems);

        var _this = _possibleConstructorReturn(this, (SkiplistItems.__proto__ || Object.getPrototypeOf(SkiplistItems)).call(this, props));

        _this.state = {};
        return _this;
    }

    _createClass(SkiplistItems, [{
        key: "render",
        value: function render() {
            var that = this;
            var rows = this.props.items.join("\n");

            var styles = {
                width: '100%'
            };

            return React.createElement("textarea", { className: "form-control", style: styles, rows: 10, defaultValue: rows, onChange: this.props.onChange.bind(this, "items", this.props.name) });
        }
    }]);

    return SkiplistItems;
}(React.Component);
//# sourceMappingURL=SkiplistItems.js.map

"use strict";

if (window.location.pathname.match(/site\/.*?\/edit/i)) {
    window.onload = function () {
        var site = document.getElementById("site-edit").getAttribute("data-site");
        ReactDOM.render(React.createElement(SiteEdit, { name: site }), document.getElementById('site-edit'));
    };
}

if (window.location.pathname.match(/^\/auto\//i)) {
    var site = document.getElementById("auto-settings-filter").getAttribute("data-section");
    ReactDOM.render(React.createElement(AutoSettings, { section: site }), document.getElementById('auto-settings-filter'));
}

if (window.location.pathname.match(/^\/skiplist\/list/i)) {
    ReactDOM.render(React.createElement(SkiplistEdit, null), document.getElementById('skiplist-edit'));
}

if (window.location.pathname.match(/^\/settings\/edit/i)) {
    ReactDOM.render(React.createElement(SettingsEditor, null), document.getElementById('settings-edit'));
}

if (window.location.pathname.match(/^\/autorules/i)) {
    ReactDOM.render(React.createElement(AutoRules, null), document.getElementById('autorules-edit'));
}

function live(eventType, elementQuerySelector, cb) {
    document.addEventListener(eventType, function (event) {

        var qs = document.querySelectorAll(elementQuerySelector);

        if (qs) {
            var el = event.target,
                index = -1;
            while (el && (index = Array.prototype.indexOf.call(qs, el)) === -1) {
                el = el.parentElement;
            }

            if (index > -1) {
                cb.call(el, event);
            }
        }
    });
}

live('click', '[data-help]', function (event) {
    var path = event.target.parentNode.getAttribute("data-help");
    showHelp(path);
});
document.querySelector("#help .close").onclick = hideHelp;

function showHelp(path) {
    window.fetch('/api/doc/parse/' + path).then(function (response) {
        return response.text();
    }).then(function (text) {
        document.querySelector("#help .content").innerHTML = text;
        document.querySelector("#help").classList.add("visible");
    });
}
function hideHelp(e) {
    document.querySelector("#help").classList.remove("visible");
    e.preventDefault();
}

if (!Array.prototype.sortIgnoreCase) {
    Array.prototype.sortIgnoreCase = function () {
        return this.sort(function (a, b) {
            return a.toLowerCase().localeCompare(b.toLowerCase());
        });
    };
}
//# sourceMappingURL=TRD.js.map
