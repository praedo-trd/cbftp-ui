import React from "react";
import Loading from "./Loading";
import SiteEditSection from "./SiteEditSection";
import classNames from "classnames";
import _ from "lodash";

export default class SiteEdit extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            info: null,
            activeSection: null
        };
    }

    UNSAFE_componentWillMount() {
        var that = this;

        fetch("/api/site/" + this.props.name, { method: "get" })
            .then(function(response) {
                return response.json().then(function(data) {
                  var activeSection = null;
                  if (data.sections.length > 0) {
                      activeSection = _.sortBy(data.sections, "name")[0].name;
                  }

                  that.setState({
                      info: data,
                      activeSection: activeSection
                  });
                });
            })
            .catch(function(err) {
                // Error :(
            });
    }

    loadSiteConfig(siteName, cb) {
        // TODO: use this
    }

    UNSAFE_componentWillUpdate(nextProps, nextState) {
        if (this.state.info != null) {
            // console.log(this.state.info.sections.length);
            // console.log(nextState.info.sections.length);
            if (
                this.state.activeSection == nextState.activeSection ||
                this.state.info.sections.length !=
                    nextState.info.sections.length
            ) {
                console.log("Saving");

                fetch("/api/site/" + this.props.name + "/save",
                {
                  method: "POST",
                  body: JSON.stringify(nextState.info),
                  headers: new Headers({
                      'Content-Type': 'application/json'
                  })
                })
                .then(function(res){ return res.json(); })
                .then(function(data){
                    console.log("Saved");
                }).catch(function (err) {
                  console.log("Error saving", err);
                });
            }
        }
    }

    addSection() {
        var newSection = prompt("Enter section (directory) name:");
        if (newSection !== null && newSection.length) {

            var found = false;
            _.forOwn(this.state.info.sections, function(value, key) {
                if(value.name.toLowerCase() == newSection.toLowerCase()) {
                  found = true;
                }
            } );

            if(found) {
              alert("Section already exists");
              return;
            }

            var newState = JSON.parse(JSON.stringify(this.state.info));
            newState.sections.push({
                name: newSection,
                bnc: null,
                pretime: 5,
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

    onDeleteSection(section, event) {
        if (
            confirm(
                "Are you sure you want to permanently remove this section: " +
                    section +
                    "?"
            )
        ) {
            var newState = JSON.parse(JSON.stringify(this.state.info));

            var sectionKey = _.findKey(newState.sections, ["name", section]);

            if (sectionKey >= 0) {
                newState.sections.splice(sectionKey, 1);

                this.setState({
                    info: newState
                });

                if (section == this.state.activeSection) {
                    this.setState({
                        activeSection: _.sortBy(
                            this.state.info.sections,
                            "name"
                        )[0].name
                    });
                }
            }
        }
        event.stopPropagation();
        event.preventDefault();
    }

    onChangeSection(newSection, e) {
        this.setState({
            activeSection: newSection
        });
        e.preventDefault();
        return false;
    }

    onChangeConfig(key, event) {
        var newState = JSON.parse(JSON.stringify(this.state.info));

        if (_.has(newState, key)) {
        }

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

    sortAffils() {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        _.set(newState, "affils", _.uniq(this.state.info.affils.sort()));
        this.setState({
            info: newState
        });
    }

    sortBannedGroups() {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        _.set(newState, "banned_groups", _.uniq(this.state.info.banned_groups.sort()));
        this.setState({
            info: newState
        });
    }

    onChangeSectionConfig(section, key, event) {
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

    onChangeSectionConfigRules(value) {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        var sectionKey = _.findKey(newState.sections, [
            "name",
            this.state.activeSection
        ]);
        if (sectionKey >= 0) {
            value = _.without(value.trim().split("\n"), "");
            newState.sections[sectionKey]["rules"] = value;
            this.setState({
                info: newState
            });
        }
    }

    onChangeSectionDupeRules(k, event) {

        var newState = JSON.parse(JSON.stringify(this.state.info));
        var sectionKey = _.findKey(newState.sections, [
            "name",
            this.state.activeSection
        ]);
        if (sectionKey >= 0) {

            switch(k) {
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

    onAddTag(tag) {
        var trigger = prompt("Choose a regex trigger", "/.*/i");
        if (trigger.length) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, [
                "name",
                this.state.activeSection
            ]);
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

    onRemoveTag(tag) {
        if (confirm("Are you sure you want to remove this tag?")) {
            var newState = JSON.parse(JSON.stringify(this.state.info));
            var sectionKey = _.findKey(newState.sections, [
                "name",
                this.state.activeSection
            ]);
            var tagKey = _.findKey(newState.sections[sectionKey].tags, [
                "tag",
                tag
            ]);
            newState.sections[sectionKey].tags.splice(tagKey, 1);
            this.setState({
                info: newState
            });
        }
    }

    onChangeTag(tag, newValues) {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        var sectionKey = _.findKey(newState.sections, [
            "name",
            this.state.activeSection
        ]);
        var tagKey = _.findKey(newState.sections[sectionKey].tags, [
            "tag",
            tag
        ]);

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

    onAddSkiplist(skiplist) {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        var sectionKey = _.findKey(newState.sections, [
            "name",
            this.state.activeSection
        ]);
        if (
            this.state.info.sections[sectionKey].skiplists.indexOf(skiplist) ===
            -1
        ) {
            newState.sections[sectionKey].skiplists.push(skiplist);
            this.setState({
                info: newState
            });
        }
    }

    onRemoveSkiplist(skiplist) {
        var newState = JSON.parse(JSON.stringify(this.state.info));
        var sectionKey = _.findKey(newState.sections, [
            "name",
            this.state.activeSection
        ]);
        var skiplistKey = this.state.info.sections[
            sectionKey
        ].skiplists.indexOf(skiplist);
        newState.sections[sectionKey].skiplists.splice(skiplistKey, 1);
        this.setState({
            info: newState
        });
    }

    testAnnounceString(key) {
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

        fetch("/api/site/" + this.props.name + "/testString",
        {
          method: "POST",
          body: JSON.stringify({
              testString: testString,
              key: key
          }),
          headers: new Headers({
              'Content-Type': 'application/json'
          })
        })
        .then(function(res){ return res.json(); })
        .then(function(data){
          if (data.matched) {
              alert(
                  "Matched section: " +
                      data.section +
                      " and rlsname: " +
                      data.rlsname
              );
          } else {
              alert("No match");
          }
        }).catch(function (err) {
          console.log("Error saving", err);
        });
    }

    render() {
        var that = this;

        if (this.state.info === null) {
            return <div id="site-edit">
              <Loading />
            </div>;
        }

        var sectionList = _.sortBy(
            this.state.info.sections,
            "name"
        ).map(function(section) {
            
            var anchorClasses = classNames({
              "nav-link": true,
              active: that.state.activeSection == section.name,
              notags: section.tags.length == 0 ||
                  typeof section.rules == "undefined" ||
                  (section.rules.length == 0 && section.tags.length == 0),
            });

            return (
                  <a key={section.name} href="#" className={anchorClasses} onClick={that.onChangeSection.bind(that, section.name)}>

                        {section.name}
                        {" "}
                        (
                        {section.tags.length}
                        )
                        {" "}
                        <i
                            onClick={that.onDeleteSection.bind(
                                that,
                                section.name
                            )}
                            className="icon-times"
                            style={{ float: "right" }}
                        />
                      </a>
            );
        });

        var sectionDOM = null;
        if (this.state.activeSection != null) {
            var section = null;
            for (var i = 0; i < this.state.info.sections.length; i++) {
                if (
                    this.state.info.sections[i].name == this.state.activeSection
                ) {
                    section = this.state.info.sections[i];
                }
            }

            if (section != null) {
                sectionDOM = (
                    <SiteEditSection
                        info={section}
                        onChange={this.onChangeSectionConfig.bind(this)}
                        onAddTag={this.onAddTag.bind(this)}
                        onRemoveTag={this.onRemoveTag.bind(this)}
                        onChangeTag={this.onChangeTag.bind(this)}
                        onAddSkiplist={this.onAddSkiplist.bind(this)}
                        onRemoveSkiplist={this.onRemoveSkiplist.bind(this)}
                        onChangeSectionRules={this.onChangeSectionConfigRules.bind(
                            this
                        )}
                        onChangeSectionDupeRules={this.onChangeSectionDupeRules.bind(this)}
                    />
                );
            }
        }

        return (
            <div id="site-edit">
                <h1>Editing site: {this.props.name}</h1>

                <div>
                    <h4 className="mb-3 mt-3">
                        Basic Information
                        {" "}
                        <button data-help="sites" className="float-end btn btn-outline-secondary">
                            <i className="icon-question-circle" /> Help
                        </button>
                    </h4>
                    <div className="row align-items-center">
                      <div className="col-12">
                        <div className="form-check form-switch">
                                <input
                                    className="form-check-input"
                                    tabIndex="1"
                                    id="site-enabled"
                                    type="checkbox"
                                    value="1"
                                    defaultChecked={this.state.info.enabled}
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "enabled"
                                    )}
                                />
                                <label className="form-check-label">Enabled?</label>
                        </div>
                      </div>
                    </div>
                </div>
                
                <hr className="my-4" />

                <fieldset>
                    <h4 className="mb-3">
                        IRC Configuration
                    </h4>

                    <div className="row g-3">
                      <div className="col-12">
                        <div className="row mb-3">
                            <label
                                htmlFor="inputIrcChannel"
                                className="col-sm-2 control-label"
                            >
                                Channel
                            </label>
                            <div className="col-sm-4">
                                <input
                                    type="text"
                                    className="form-control form-control-code"
                                    id="inputIrcChannel"
                                    placeholder="Regex to match channel"
                                    value={this.state.info.irc.channel}
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.channel"
                                    )}
                                />
                                <div className="form-text form-text-inline">(Regular expression)</div>
                            </div>
                        </div>
                        <div className="row mb-3">
                            <label
                                htmlFor="inputIrcBot"
                                className="col-sm-2 control-label"
                            >
                                Bot
                            </label>
                            <div className="col-sm-4">
                                <input
                                    type="text"
                                    className="form-control form-control-code"
                                    id="inputIrcBot"
                                    placeholder="Regex to match bot"
                                    value={this.state.info.irc.bot}
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.bot"
                                    )}
                                />
                                <div className="form-text form-text-inline">(Regular expression)</div>
                            </div>
                        </div>
                    </div>
                  </div>

                </fieldset>
                
                <hr className="my-4" />
                
                <fieldset>
                    <h4 className="mb-3">
                        Announce strings
                    </h4>

                    <div>

                        <div className="row mb-3 align-items-center">
                            <div className="col-sm-7" />
                            <div className="col-sm-1">
                                <strong>Section</strong>
                            </div>
                            <div className="col-sm-1">
                                <strong>Rlsname</strong>
                            </div>
                            <div className="col-md-2" />
                        </div>
                        <div className="row mb-3 align-items-center">
                            <label
                                htmlFor="inputNewString"
                                className="col-sm-1 control-label"
                            >
                                New
                            </label>
                            <div className="col-sm-6">
                                <input
                                    type="text"
                                    className="form-control"
                                    id="inputNewString"
                                    placeholder="e.g. New in &section by &user with &release"
                                    value={
                                        this.state.info.irc.strings.newstring
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.newstring"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Section"
                                    value={
                                        this.state.info.irc.strings[
                                            "newstring-section"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.newstring-section"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Rls"
                                    value={
                                        this.state.info.irc.strings[
                                            "newstring-rls"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.newstring-rls"
                                    )}
                                />
                            </div>
                            <div className="col-md-2">
                                <div className="form-check">
                                  <input
                                      type="checkbox"
                                      value=""
                                      defaultChecked={
                                          this.state.info.irc.strings[
                                              "newstring-isregex"
                                          ]
                                      }
                                      onChange={this.onChangeConfig.bind(
                                          this,
                                          "irc.strings.newstring-isregex"
                                      )}
                                      className="form-check-input"
                                  />
                                  <label className="form-check-label">Regex?</label>
                                </div>
                            </div>
                            <div className="col-md-1">
                                <a
                                    className="btn btn-outline-secondary btn-block"
                                    onClick={this.testAnnounceString.bind(
                                        this,
                                        "irc.strings.newstring"
                                    )}
                                >
                                    Test
                                </a>
                            </div>
                        </div>
                        <div className="row mb-3 align-items-center">
                            <label
                                htmlFor="inputEndString"
                                className="col-sm-1 control-label"
                            >
                                End
                            </label>
                            <div className="col-sm-6">
                                <input
                                    type="text"
                                    className="form-control"
                                    id="inputEndString"
                                    placeholder="e.g. End in &section by &user with &release"
                                    value={
                                        this.state.info.irc.strings["endstring"]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.endstring"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Tok #"
                                    value={
                                        this.state.info.irc.strings[
                                            "endstring-section"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.endstring-section"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Tok #"
                                    value={
                                        this.state.info.irc.strings[
                                            "endstring-rls"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.endstring-rls"
                                    )}
                                />
                            </div>
                            <div className="col-md-2">
                                <div className="form-check">
                                        <input
                                            type="checkbox"
                                            value=""
                                            defaultChecked={
                                                this.state.info.irc.strings[
                                                    "endstring-isregex"
                                                ]
                                            }
                                            onChange={this.onChangeConfig.bind(
                                                this,
                                                "irc.strings.endstring-isregex"
                                            )}
                                            className="form-check-input"
                                        />
                                        <label className="form-check-label">Regex?</label>
                                </div>
                            </div>
                            <div className="col-md-1">
                                <a
                                    className="btn btn-outline-secondary btn-block"
                                    onClick={this.testAnnounceString.bind(
                                        this,
                                        "irc.strings.endstring"
                                    )}
                                >
                                    Test
                                </a>
                            </div>
                        </div>
                        <div className="row mb-3 align-items-center">
                            <label
                                htmlFor="inputPreString"
                                className="col-sm-1 control-label"
                            >
                                Pre
                            </label>
                            <div className="col-sm-6">
                                <input
                                    type="text"
                                    className="form-control"
                                    id="inputPreString"
                                    placeholder="e.g. Pre in &section by &user with &release"
                                    value={
                                        this.state.info.irc.strings["prestring"]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.prestring"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Tok #"
                                    value={
                                        this.state.info.irc.strings[
                                            "prestring-section"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.prestring-section"
                                    )}
                                />
                            </div>
                            <div className="col-md-1">
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Tok #"
                                    value={
                                        this.state.info.irc.strings[
                                            "prestring-rls"
                                        ]
                                    }
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "irc.strings.prestring-rls"
                                    )}
                                />
                            </div>
                            <div className="col-md-2">
                                <div className="form-check">
                                        <input
                                            type="checkbox"
                                            value=""
                                            defaultChecked={
                                                this.state.info.irc.strings[
                                                    "prestring-isregex"
                                                ]
                                            }
                                            onChange={this.onChangeConfig.bind(
                                                this,
                                                "irc.strings.prestring-isregex"
                                            )}
                                            className="form-check-input"
                                        />
                                        <label className="form-check-label">Regex?</label>
                                </div>
                            </div>
                            <div className="col-md-1">
                                <a
                                    className="btn btn-outline-secondary btn-block"
                                    onClick={this.testAnnounceString.bind(
                                        this,
                                        "irc.strings.prestring"
                                    )}
                                >
                                    Test
                                </a>
                            </div>
                        </div>
                    </div>

                </fieldset>
                
                <hr className="my-4" />

                <fieldset>
                    <h4 className="mb-3">
                        Groups
                    </h4>

                    <div>
                        <div className="row mb-3 align-items-center">
                            <label
                                htmlFor="inputAffils"
                                className="col-sm-2 control-label"
                            >
                                Affils
                            </label>
                            <div className="col-sm-8">
                                <input
                                    type="text"
                                    className="form-control"
                                    id="inputAffils"
                                    placeholder="List of affils (seperated by spaces)"
                                    value={this.state.info.affils.join(" ")}
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "affils"
                                    )}
                                    style={{textTransform: "uppercase"}}
                                />
                            </div>
                            <div className="col-sm-2">
                                <a
                                    className="btn btn-block btn-outline-secondary"
                                    onClick={this.sortAffils.bind(this)}
                                >
                                    Sort A-Z
                                </a>
                            </div>
                        </div>
                        <div className="row mb-3 align-items-center">
                            <label
                                htmlFor="inputBannedGroups"
                                className="col-sm-2 control-label"
                            >
                                Banned groups
                            </label>
                            <div className="col-sm-8">
                                <input
                                    type="text"
                                    className="form-control"
                                    id="inputBannedGroups"
                                    placeholder="List of banned groups (seperated by spaces)"
                                    value={this.state.info.banned_groups.join(" ")}
                                    onChange={this.onChangeConfig.bind(
                                        this,
                                        "banned_groups"
                                    )}
                                    style={{textTransform: "uppercase"}}
                                />
                            </div>
                            <div className="col-sm-2">
                                <a
                                    className="btn btn-block btn-outline-secondary"
                                    onClick={this.sortBannedGroups.bind(this)}
                                >
                                    Sort A-Z
                                </a>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <fieldset>
                    <legend>Sections</legend>

                    <div className="row">
                        <div className="col-md-3">
                            <ul
                                className="nav flex-column nav-pills"
                                style={{ marginBottom: "1em" }}
                            >
                                {sectionList}
                            </ul>
                            <a
                                className="btn btn-block btn-outline-secondary"
                                onClick={this.addSection.bind(this)}
                            >
                                Add section
                            </a>
                        </div>
                        <div className="col-md-9">
                            {sectionDOM}
                        </div>
                    </div>
                </fieldset>
            </div>
        );
    }
}
