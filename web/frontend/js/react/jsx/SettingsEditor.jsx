import React from "react";
import Loading from "./Loading";
import InputTagOption from "./InputTagOption";
import InputBooleanChoice from "./InputBooleanChoice";
import InputText from "./InputText";
import InputTextarea from "./InputTextarea";
import classNames from "classnames";
import _ from "lodash";


const days = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday",
];

const SettingsTagList = ({ tags, activeTag, onChange, onDelete }) =>
  Object.keys(tags)
    .sortIgnoreCase()
    .map(function (key) {
      const tag = tags[key];

      const classes = classNames({
        active: activeTag == key,
        "nav-link": true,
      });

      return (
        <a
          key={key}
          className={classes}
          href="#"
          onClick={(e) => onChange(key, e)}
        >
          {key}{" "}
          <i
            onClick={(e) => onDelete(key, e)}
            className="fa fa-times"
            style={{ float: "right" }}
          ></i>
        </a>
      );
    });

const SettingsTags = () => {
  return <></>;
};

const SettingsSchedule = ({ existingSchedule, onChangeSchedule }) => {
  const dayRows = days.map(function (day, idx) {
    return (
      <tr key={day}>
        <td>{day}</td>
        <td style={{ width: "200px" }}>
          <input
            onChange={(e) => onChangeSchedule(idx + 1, 0, e)}
            type="text"
            className="form-control input-sm"
            style={{ width: "70px", marginRight: 0, display: "inline-block" }}
            defaultValue={existingSchedule[idx + 1][0]}
          />{" "}
          -
          <input
            onChange={(e) => onChangeSchedule(idx + 1, 1, e)}
            type="text"
            className="form-control input-sm"
            style={{ width: "70px", display: "inline-block" }}
            defaultValue={existingSchedule[idx + 1][1]}
          />
        </td>
      </tr>
    );
  });

  return (
    <>
      <h4 className="mb-3">Scheduling</h4>
      <p>
        If you enable cbftp integration, you can limit its schedule below so UDP
        commands are only sent between certain hours.
      </p>
      <table className="table table-bordered" style={{ width: "auto" }}>
        <tbody>{dayRows}</tbody>
      </table>
    </>
  );
};

export default class SettingsEditor extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      info: null,
      activeSection: null,
    };
  }

  UNSAFE_componentWillMount() {
    const that = this;

    fetch("/api/settings", { method: "get" })
      .then(function (response) {
        return response.json().then(function (json) {
          that.setState({
            info: json,
            activeTag: Object.keys(json.tag_options).sort()[0],
          });
        });
      })
      .catch(function (err) {
        // Error :(
      });
  }

  loadSiteConfig(siteName, cb) {
    // TODO: use this
  }

  UNSAFE_componentWillUpdate(nextProps, nextState) {
    if (this.state.info != null) {
      if (this.state.activeTag == nextState.activeTag) {
        console.log("Saving");

        var payload = new FormData();
        payload.append(
          "allowed_classifications",
          this.state.setClassifications
        );
        payload.append("allowed_countries", this.state.setCountries);

        fetch("/api/settings/save", {
          method: "POST",
          body: JSON.stringify(nextState.info),
          headers: {
            "user-agent": "Mozilla/4.0 MDN Example",
            "content-type": "application/json; charset=utf-8",
          },
        })
          .then(function (res) {
            return res.json();
          })
          .then(function (data) {
            console.log("Saved");
          })
          .catch(function (err) {
            console.log("Error", err);
          });
      }
    }
  }

  onChangeSetting(key, event) {
    var newState = JSON.parse(JSON.stringify(this.state.info));

    if (_.has(newState, key)) {
    }

    switch (key) {
      case "require_pretime":
      case "always_add_affils":
      case "dataprovider_needs_approval":
      case "refresh_ended_shows":
      case "cleanup_old_pre":
      case "baddir_skip_pre":
      case "approved_straight_to_cbftp":
        const isTrue = event.target.value == "true";
        _.set(newState, key, isTrue);
        break;

      case "banned_groups":
        _.set(newState, key, event.target.value.toUpperCase().split(" "));
        break;

      case "default_skiplists":
      case "tags":
      case "ignore_tags":
        _.set(newState, key, event.target.value.split(" "));
        break;

      case "children_networks":
        _.set(newState, key, event.target.value.split(","));
        break;

      case "pre_retention":
        _.set(newState, key, parseInt(event.target.value));
        break;

      default:
        _.set(newState, key, event.target.value);
    }

    this.setState({
      info: newState,
    });
  }

  onChangeTag(newTag, e) {
    this.setState({
      activeTag: newTag,
    });
    e.preventDefault();
    return false;
  }

  onDeleteTag() {}
  
  addTag() {
    const tag = prompt("Enter a tag name");
    if (tag != null && tag.length) {
      if (
        Object.keys(this.state.info.tag_options)
          .map(Function.prototype.call, String.prototype.toLowerCase)
          .includes(tag.toLowerCase())
      ) {
        alert("This tag already exists");
        return;
      }

      var newState = JSON.parse(JSON.stringify(this.state.info));
      newState.tag_options[tag] = {
        data_sources: [],
        allowed_groups: [],
        tag_requires: [],
        tag_skiplist: [],
      };
      this.setState({
        info: newState,
      });
    }
  }

  onChangeTagOptions(key, event) {
    var newState = JSON.parse(JSON.stringify(this.state.info));

    const lastBit = key.split(".").slice(-1).pop();

    switch (lastBit) {
      case "data_sources":
        _.set(newState, key, event.target.value.split(" "));
        break;
      case "allowed_groups":
        if (!event.target.value.trim().length) {
          _.set(newState, key, []);
        } else {
          _.set(newState, key, event.target.value.toUpperCase().split(" "));
        }
        break;

      case "tag_requires":
      case "tag_skiplist":
        _.set(newState, key, event.target.value.split("\n"));
        break;

      default:
      //  console.log(key, event.target.value);
      //_.set(newState, key, event.target.value);
    }

    //console.log(newState);return;

    this.setState({
      info: newState,
    });
  }

  onChangeSchedule(dayIndex, dayIndexKey, e) {
    var newState = JSON.parse(JSON.stringify(this.state.info));

    _.set(
      newState.schedule,
      dayIndex + ".[" + dayIndexKey + "]",
      e.target.value
    );

    this.setState({
      info: newState,
    });
  }

  render() {
    const that = this;

    // loading if we have nothing to display
    if (this.state.info === null) {
      return <Loading />;
    }

    // handle the switching of tag options
    let tagDOM = null;
    if (this.state.activeTag != null) {
      const tagInfo = this.state.info.tag_options[this.state.activeTag];

      if (tagInfo != null) {
        tagDOM = (
          <InputTagOption
            key={this.state.activeTag}
            tag={this.state.activeTag}
            info={tagInfo}
            onChange={this.onChangeTagOptions.bind(this)}
          />
        );
      }
    }

    // fix for broken data model :(
    if (!this.state.info.children_networks) {
      this.state.info.children_networks = [];
    }

    // some default styles
    const styles = {
      width: "60px",
    };
    const spacer = {
      display: "inline-block",
      width: "80px",
    };

    return (
      <div>
        <div className="hbd">
          <h1 className="heading">Edit settings</h1>
        </div>
        <div className="form-horizontal">
          <h4 className="mb-3">Basic settings</h4>
          <InputBooleanChoice
            name="require_pretime"
            label="Require pretime?"
            description="Bla bla"
            defaultValue={this.state.info.require_pretime}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputBooleanChoice
            name="always_add_affils"
            label="Always add affil sites to races (where possible)?"
            description="Bla bla"
            defaultValue={this.state.info.always_add_affils}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="data_exchange_channel"
            label="Data exchange channel"
            description="Bla bla"
            defaultValue={this.state.info.data_exchange_channel}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputBooleanChoice
            name="dataprovider_needs_approval"
            label="Require approval for all data provider lookups? (NOT WORKING YET)"
            description="Bla bla"
            defaultValue={this.state.info.dataprovider_needs_approval}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputBooleanChoice
            name="refresh_ended_shows"
            label="Refresh data cache for 'Ended' shows?"
            description="Bla bla"
            defaultValue={this.state.info.refresh_ended_shows}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputBooleanChoice
            name="cleanup_old_pre"
            label="Delete pre data older than 30 days?"
            description="Bla bla"
            defaultValue={this.state.info.cleanup_old_pre}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="default_skiplists"
            label="Default skiplists (space-seperator)"
            description="Bla bla"
            defaultValue={this.state.info.default_skiplists.sort().join(" ")}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="pre_retention"
            label="Retain pres for x days? (0 disables)"
            description="Bla bla"
            defaultValue={this.state.info.pre_retention}
            onChange={this.onChangeSetting.bind(this)}
          />

          <hr className="my-4" />

          <h4 className="mb-3">Bad shit</h4>
          <InputText
            name="baddir"
            label="Regex to skip bad release names"
            description="Bla bla"
            defaultValue={this.state.info.baddir}
            onChange={this.onChangeSetting.bind(this)}
            classes="code"
          />
          <InputBooleanChoice
            name="baddir_skip_pre"
            label="Skip adding baddir matches to pre database?"
            description="Bla bla"
            defaultValue={this.state.info.baddir_skip_pre || false}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="banned_groups"
            label="List of banned groups (space-seperator)"
            description="Bla bla"
            defaultValue={this.state.info.banned_groups.sort().join(" ")}
            onChange={this.onChangeSetting.bind(this)}
          />
          <hr className="my-4" />
          <h4 className="mb-3">Tags (bookmarks)</h4>
          <InputText
            name="tags"
            label="Tags (space-seperator)"
            description="Bla bla"
            defaultValue={this.state.info.tags.sort().join(" ")}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="ignore_tags"
            label="Tags to ignore (space-seperator)"
            description="Bla bla"
            defaultValue={this.state.info.ignore_tags.sort().join(" ")}
            onChange={this.onChangeSetting.bind(this)}
          />
          <hr className="my-4" />
          <h4 className="mb-3">Tag options</h4>
          <div className="row">
            <div className="col-md-3">
              <div className="tag-list-vertical">
                <div
                  className="nav flex-column nav-pills"
                  style={{ marginBottom: "1em" }}
                >
                  <SettingsTagList
                    tags={this.state.info.tag_options}
                    activeTag={this.state.activeTag}
                    onChange={this.onChangeTag.bind(this)}
                    onDelete={this.onDeleteTag.bind(this)}
                  />
                </div>
              </div>
              <a
                className="btn btn-block btn-outline-secondary"
                onClick={this.addTag.bind(this)}
              >
                Add tag
              </a>
            </div>
            <div className="col-md-9">{tagDOM}</div>
          </div>
          <hr className="my-4" />
          <h4 className="mb-3">Misc. options</h4>
          <InputText
            name="children_networks"
            label="List of children's networks (comma-seperator)"
            description="CBBC,Cartoon Network"
            defaultValue={this.state.info.children_networks.sort().join(",")}
            onChange={this.onChangeSetting.bind(this)}
          />
          <hr className="my-4" />
          <h4 className="mb-3">cbftp</h4>
          <InputText
            name="cbftp_host"
            label="Hostname"
            description="Hostname"
            defaultValue={this.state.info.cbftp_host}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="cbftp_password"
            label="API password"
            description=""
            defaultValue={this.state.info.cbftp_password}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="cbftp_port"
            label="UDP Port"
            description="Port"
            defaultValue={this.state.info.cbftp_port}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputText
            name="cbftp_api_port"
            label="HTTPS/JSON API Port"
            description="Port"
            defaultValue={this.state.info.cbftp_api_port}
            onChange={this.onChangeSetting.bind(this)}
          />
          <InputBooleanChoice
            name="approved_straight_to_cbftp"
            label="Send approved races straight to cbftp?"
            description="Bla bla"
            defaultValue={this.state.info.approved_straight_to_cbftp}
            onChange={this.onChangeSetting.bind(this)}
          />
          <hr className="my-4" />
          <SettingsSchedule
            existingSchedule={this.state.info.schedule}
            onChangeSchedule={this.onChangeSchedule.bind(this)}
          />
        </div>
      </div>
    );
  }
}
