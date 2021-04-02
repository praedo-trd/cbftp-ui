import React from "react";
import RulesEditor from "./RulesEditor";
import classNames from "classnames";
import _ from "lodash";

export default class SiteEditSection extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            tags: [],
            skiplists: [],
            activeTab: "general",
            editingTag: null
        };

    }

    UNSAFE_componentWillMount() {

        var that = this;

        fetch("/api/section", { method: "get" })
            .then(function(response) {
                return response.json().then(function(data) {
                  that.setState({
                      tags: data
                  });
                });
            })
            .catch(function(err) {
                // Error :(
            });

            fetch("/api/skiplist", { method: "get" })
                .then(function(response) {
                    return response.json().then(function(data) {
                      that.setState({
                          skiplists: data
                      });
                    });
                })
                .catch(function(err) {
                    // Error :(
                });

    }
    
    // componentDidUpdate(prevProps, prevState) {
    //   if(prevProps.info.name != this.props.info.name) {
    //     this.setState({
    //       editingTag: null
    //     });
    //   }
    // }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if(this.props.info.name != nextProps.info.name) {
            this.setState({
                editingTag: null
            });
        }
    }


    onChangeTab(tab, event) {
        this.setState({
            activeTab: tab
        });
        event.preventDefault();
    }

    /* tag change stuff */
    onChooseTagToEdit(tag, event) {
        this.setState({
            editingTag: tag
        });
    }

    onToggleTag(tag, checked) {
        if(checked == false) {
            this.props.onRemoveTag(tag);
            this.setState({
                editingTag: null
            });
        }
        else {
            this.props.onAddTag(tag);
            this.setState({
                editingTag: null
            });
        }
    }

    onChangeTrigger(event) {
        this.props.onChangeTag(this.state.editingTag, {trigger: event.target.value});
    }

    onChangeTagRule(rules) {
        this.props.onChangeTag(this.state.editingTag, {rules: rules});
    }

    /* skiplist change stuff */
    onToggleSkiplist(skiplist, checked) {
        if(checked == false) {
            this.props.onRemoveSkiplist(skiplist);
        }
        else {
            this.props.onAddSkiplist(skiplist);
        }
    }

    render() {

        var that = this;

        var tagList = null;
        if(this.state.tags.length) {
            tagList = this.state.tags.map(function(tag) {

                var tickOrCross = <i className="icon-times" style={{color: "#666"}} onClick={that.onToggleTag.bind(that,tag, true)}></i>;
                var btn = null;
                for(var i = 0; i < that.props.info.tags.length; i++) {
                    if(that.props.info.tags[i].tag == tag) {
                        tickOrCross = <i className="icon-check" style={{color: "#00E500"}}  onClick={that.onToggleTag.bind(that,tag, false)}></i>;
                        btn = <span> - <a href="#/" onClick={
                            that.onChooseTagToEdit.bind(that, tag)
                        } className="btn btn-outline-secondary btn-xs">Edit</a></span>
                    }
                }

                return (
                    <div className="col-md-4" key={tag}>
                        {tickOrCross} <code>{tag}</code> {btn}
                    </div>
                );
            });
        }

        var skiplistList = null;
        if(Object.keys(this.state.skiplists).length) {
            skiplistList = Object.keys(this.state.skiplists).map(function(skiplist) {

                var tickOrCross = <i className="icon-times" style={{color: "#666"}} onClick={that.onToggleSkiplist.bind(that,skiplist, true)}></i>;
                for(var i = 0; i < that.props.info.skiplists.length; i++) {
                    if(that.props.info.skiplists[i] == skiplist) {
                        tickOrCross = <i className="icon-check" style={{color: "#00E500"}}  onClick={that.onToggleSkiplist.bind(that,skiplist, false)}></i>;
                    }
                }

                return (
                    <div className="col-md-6" key={skiplist}>
                        {tickOrCross} <code>{skiplist}</code>
                    </div>
                );
            });
        }

        var triggerEditor = null;
        if(this.state.editingTag !== null) {
            var tagKey = _.findKey(this.props.info.tags, ['tag', this.state.editingTag]);
            var trigger = this.props.info.tags[tagKey].trigger;
            triggerEditor = (
                <div style={{marginTop: "1em"}}>
                    <label htmlFor="exampleInputName2">Trigger for tag <code>{this.state.editingTag}</code>:</label>
                    <div className="form-group">
                        <input type="text" className="form-control"
                               id="exampleInputName2" placeholder="trigger.." size="6"
                               value={trigger ? trigger : ""}
                               onChange={
                                this.onChangeTrigger.bind(this)
                               }
                        />
                    </div>
                </div>
            )
        }

        var tagRuleEditor = null;
        if(this.state.editingTag !== null) {
            var tagKey = _.findKey(this.props.info.tags, ['tag', this.state.editingTag]);
            var rules = this.props.info.tags[tagKey].rules;
            let rulesValue = "";
            if(typeof rules != 'undefined' && rules.length) {
                rulesValue = rules.join('\n');
            }
            tagRuleEditor = (
                <div style={{marginTop: "1em"}}>
                    <label>Rules for tag <code>{this.state.editingTag}</code>:</label>
                    <div className="form-group">

                        <RulesEditor 
                          defaultValue={rulesValue} onChange={
                            this.onChangeTagRule.bind(this)
                          } 
                          keyRef={this.props.info.name + this.state.editingTag + "tagEditor"}
                          autocomplete={true}
                         />

                    </div>
                </div>
            );
        }


        var tabs = ["general", "tags", "rules", "dupes", "skiplists"];
        var tabClasses = [];
        var tabList = tabs.map(function(tab) {

            var label = tab.charAt(0).toUpperCase() + tab.slice(1);

            var badge = '';
            if(tab == "rules" && typeof that.props.info.rules != 'undefined') {
                badge = <span className="badge">{that.props.info.rules.length}</span>
            }
            else if(tab == "tags" && typeof that.props.info.tags != 'undefined') {
                badge = <span className="badge">{that.props.info.tags.length}</span>
            }
            else if(tab == "skiplists" && typeof that.props.info.skiplists != 'undefined') {
                badge = <span className="badge">{that.props.info.skiplists.length}</span>
            }

            var classes = classNames({
              "nav-item": true,
            });

            tabClasses[tab] = classNames({
                "tab-panel": true,
                "tab-panel-visible": that.state.activeTab == tab
            });
            
            var anchorClasses = classNames({
              "nav-link": true,
              active: that.state.activeTab == tab
            });

            return (
                <li className={classes} key={tab} role="presentation" data-bs-toggle="general"><a className={anchorClasses} href="#" onClick={
                        that.onChangeTab.bind(that, tab)
                }>{label} {badge}</a></li>
            );
        });

        var rules = "";
        if(typeof this.props.info.rules != 'undefined') {
            rules = this.props.info.rules.join("\n");
        }
        
        var dupeFirstWins = this.props.info.dupeRules["source.firstWins"];

        var dupeHelpText = null;
        if(this.props.info.dupeRules["source.priority"] && this.props.info.dupeRules["source.priority"].length) {
          var dupeParts = this.props.info.dupeRules["source.priority"].split(",");
          if(dupeParts.length >= 2) {
            dupeHelpText = "Firstly " + dupeParts[0] + " is allowed, followed by " + dupeParts.slice(1).join(", ");
          }
        }
        
        return (
            <div>
                <ul className="nav nav-tabs" id="section-tabs">
                    {tabList}
                </ul>

                <div className="card card-tag">
                  <div className="card-body">
                    <div id="general" className={tabClasses['general']}>
                        <div className="row g-3 align-items-center">
                            <div className="col-auto">
                                <label htmlFor="exampleInputName2" className="col-form-label">Pretime (min.)</label>
                              </div>
                              <div className="col-auto">
                                <input type="text" className="form-control"
                                       id="exampleInputName2" placeholder="300" size="6"
                                       onChange={
                                            this.props.onChange.bind(this, this.props.info.name, "pretime")
                                       } value={this.props.info.pretime ? this.props.info.pretime : ""}
                                />
                            </div>
                            <div className="col-auto">
                                <label htmlFor="exampleInputEmail2"  className="col-form-label">BNC</label>
                              </div>
                              <div className="col-auto">
                                <input type="type" className="form-control"
                                       id="exampleInputEmail2" placeholder=""
                                       onChange={
                                                this.props.onChange.bind(this, this.props.info.name, "bnc")
                                           }  value={this.props.info.bnc ? this.props.info.bnc : ""}
                                />
                            </div>
                        </div>
                    </div>
                    <div id="tags" className={tabClasses['tags']}>
                        <div className="">
                            <label htmlFor="exampleInputName2">Choose tags:</label>
                            <div className="row">
                                {tagList}
                            </div>
                            {triggerEditor && <hr />}
                            {triggerEditor}
                            {tagRuleEditor}
                        </div>
                    </div>
                    <div id="rules" className={tabClasses['rules']}>
                        <div className="">
                            <label htmlFor="exampleInputName2" style={{display: 'block'}}>
                              Rules:
                            </label>

                          <RulesEditor 
                            keyRef={this.props.info.name + this.state.editingTag + "sectionEditor"} 
                            defaultValue={rules} 
                            autocomplete={true}
                            onChange={
                              this.props.onChangeSectionRules.bind(this)
                          } />
                        </div>
                    </div>
                    <div id="dupes" className={tabClasses['dupes']}>
                        <div className="">
                            <div className="alert alert-warning">
                              Only use on TV sections
                            </div>

                            <div className="row mb-3">
                              <div className="col-12">
                                <div className="form-check">
                                    
                                    <input type="checkbox" defaultChecked={this.props.info.dupeRules["source.firstWins"]} onClick={
                                             this.props.onChangeSectionDupeRules.bind(this, "source.firstWins")
                                      } className="form-check-input" /> 
                                      <label className="form-check-label">First on site wins?</label>
                                </div>
                              </div>
                            </div>
                            <div className="row mb-3">
                              <div className="col-12">
                                <label htmlFor="exampleInputEmail2">Priority:</label>
                                <input type="type" className="form-control"
                                       id="exampleInputEmail2" placeholder=""
                                       onChange={
                                                this.props.onChangeSectionDupeRules.bind(this, "source.priority")
                                           }  value={this.props.info.dupeRules["source.priority"] ? this.props.info.dupeRules["source.priority"] : ""}
                                />
                              {this.props.info.dupeRules["source.priority"] && <span className="help-block">{dupeHelpText}</span>}
                              </div>
                            </div>
                        </div>
                    </div>
                    <div id="skiplists" className={tabClasses['skiplists']}>
                        <div className="">
                            <label htmlFor="">Choose skiplists:</label>
                            <div className="row">
                                {skiplistList}
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        )
    }
}
