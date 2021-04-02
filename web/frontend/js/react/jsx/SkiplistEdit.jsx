import React from "react";
import Loading from "./Loading";
import SkiplistItems from "./SkiplistItems";
import SkiplistItemRegex from "./SkiplistItemRegex";
import _ from "lodash";

export default class SkiplistEdit extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            skiplists: null
        };
    }

    UNSAFE_componentWillMount() {
        var that = this;
        fetch("/api/skiplist/", { method: "get" })
            .then(function(response) {
                return response.json().then(function(json) {
                    that.setState({
                        skiplists: json,
                        loaded: true
                    });
                });
            })
            .catch(function(err) {
                // Error :(
            });
    }

    UNSAFE_componentWillUpdate(nextProps, nextState) {
        if (this.state.skiplists != null) {
            console.log("Saving");

            fetch("/api/skiplist/save",
            {
              method: "POST",
              body: JSON.stringify(nextState.skiplists),
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

    componentDidUpdate(prevProps, prevState) {
        if (prevState.loaded === false) {
            return;
        }
    }

    _checkItemDoesntExist(name) {}

    addNormal() {
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

    addRegex() {
        var name = prompt("Enter a unique name for this regex skiplist (a-z0-9_)");
        if (name && name.length) {
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

    changeItem(type, name, event) {

      var newState = JSON.parse(JSON.stringify(this.state.skiplists));

      var obj = {
        shared: true
      }
      if(type == "regex") {
        obj.regex = event.target.value;
      }
      else {
        obj.items = event.target.value.split("\n");
      }

      _.set(newState, name, obj);

      this.setState({
          skiplists: newState
      });
    }

    deleteItem(name, event) {
      if(confirm('Are you sure you want to delete the skiplist "' + name + '"?')) {
        var newState = JSON.parse(JSON.stringify(this.state.skiplists));
        delete newState[name];
        this.setState({
            skiplists: newState
        });
      }
    }

    render() {
        var that = this;

        if(this.state.skiplists === null) {
          return <Loading />
        }

        var skiplistList = null;
        if (this.state.skiplists !== null && Object.keys(this.state.skiplists).length) {
            skiplistList = Object.keys(
                this.state.skiplists
            ).map(function(skiplist) {
                var editor = null;
                var isRegex = null;
                if (
                    typeof that.state.skiplists[skiplist].items !== "undefined"
                ) {
                    var editor = (
                        <SkiplistItems
                            name={skiplist}
                            items={that.state.skiplists[skiplist].items}
                            onChange={that.changeItem.bind(that)}
                        />
                    );
                } else {
                    var editor = (
                        <SkiplistItemRegex
                            name={skiplist}
                            regex={that.state.skiplists[skiplist].regex}
                            onChange={that.changeItem.bind(that)}
                        />
                    );
                    isRegex = <span> (regex)</span>;
                }

                return (
                    <div className="row" key={skiplist}>
                        <div className="col-md-3">
                            <code>{skiplist}</code> {isRegex}<br /><br />
                            <a class="btn btn-outline-secondary" onClick={that.deleteItem.bind(that, skiplist)}>Delete</a>
                        </div>
                        <div className="col-md-9">
                            {editor}
                            <br />
                        </div>
                    </div>
                );
            });
        }

        return (
          <>
            <div className="hbd">
                <h1 className="heading">
                  Skiplists
                </h1>
                  <div className="controls">
                      <a data-help="skiplists" className="btn btn-outline-secondary"><i className="icon-question-circle"></i> Help</a>
                      {" "}
                      <a
                          className="btn btn-primary"
                          onClick={this.addRegex.bind(this)}
                      >
                          + Add regex item
                      </a>
                      {" "}
                      <a
                          className="btn btn-primary"
                          onClick={this.addNormal.bind(this)}
                      >
                          + Add wildcard item
                      </a>
                  </div>
            </div>
            <br />
            <div>
            {skiplistList}
            </div>
            </>
        );
    }
}
