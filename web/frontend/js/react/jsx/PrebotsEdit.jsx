import React from "react";
import Loading from "./Loading";
import PrebotsEditForm from "./PrebotsEditForm";

export default class PrebotsEdit extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      prebots: null,
    };
  }

  UNSAFE_componentWillMount() {
    var that = this;
    fetch("/api/prebots/", { method: "get" })
      .then(function (response) {
        return response.json().then(function (json) {
          that.setState({
            prebots: json,
            loaded: true,
          });
        });
      })
      .catch(function (err) {
        // Error :(
      });
  }

  UNSAFE_componentWillUpdate(nextProps, nextState) {
    if (this.state.prebots != null) {
      console.log("Saving");

      fetch("/api/prebots/save", {
        method: "POST",
        body: JSON.stringify(nextState.prebots),
        headers: new Headers({
          "Content-Type": "application/json",
        }),
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          console.log("Saved");
        })
        .catch(function (err) {
          console.log("Error saving", err);
        });
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (prevState.loaded === false) {
      return;
    }
  }

  addBot() {
    const channel = prompt("Enter a regex to match the channel");
    const bot = prompt("Enter a regex to match the bot");
    const stringMatch = prompt("Enter a regex to match the rlsname");

    if (
      !channel ||
      !channel.length ||
      !bot ||
      !bot.length ||
      !stringMatch ||
      !stringMatch.length
    ) {
      return alert("You didn't fill in all information");
    }

    const exists = this.state.prebots.some(
      (prebot) => prebot.channel === channel && prebot.bot === bot
    );

    if (exists) {
      return alert("Looks like this bot is already added");
    }

    var newObj = this.state.prebots;
    newObj.push({
      channel: channel,
      bot: bot,
      string_match: stringMatch,
    });
    this.setState({
      prebots: newObj,
    });
  }

  onChange(idx, field, value) {
    var newState = JSON.parse(JSON.stringify(this.state.prebots));

    newState[idx][field] = value;

    this.setState({
      prebots: newState,
    });
  }

  onDelete(idx) {
    var newState = JSON.parse(JSON.stringify(this.state.prebots));

    newState.splice(idx, 1);

    this.setState({
      prebots: newState,
    });
  }

  deleteItem(name, event) {
    // if(confirm('Are you sure you want to delete the skiplist "' + name + '"?')) {
    //   var newState = JSON.parse(JSON.stringify(this.state.skiplists));
    //   delete newState[name];
    //   this.setState({
    //       skiplists: newState
    //   });
    // }
  }

  render() {
    var that = this;

    if (this.state.prebots === null) {
      return <Loading />;
    }

    console.log(this.state.prebots);

    const list = this.state.prebots.map((prebot, idx) => (
      <div className="row" key={`prebot${idx}`}>
        <PrebotsEditForm
          index={idx}
          data={prebot}
          onChange={this.onChange.bind(this)}
          onDelete={this.onDelete.bind(this)}
        />
      </div>
    ));

    return (
      <>
        <div className="hbd">
          <h1 className="heading">Prebots</h1>
          <div className="controls">
            <a data-help="prebots" className="btn btn-outline-secondary">
              <i className="icon-question-circle"></i> Help
            </a>{" "}
            <a className="btn btn-primary" onClick={this.addBot.bind(this)}>
              + Add prebot
            </a>
          </div>
        </div>
        <br />
        <div className="container">
          <div className="alert alert-warning">
            Make sure your regexes are valid using regex101.com
          </div>
          {list}
        </div>
      </>
    );
  }
}
