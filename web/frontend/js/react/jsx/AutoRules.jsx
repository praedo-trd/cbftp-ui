import React, { useState } from "react";
import Loading from "./Loading";
import RulesEditor from "./RulesEditor";
import _ from "lodash";

const fetchAutoRules = async () => {
  const rules = fetch('/api/autorules', {method: 'get'});
  if (rules && rules.length) {
    return rules.json();
  }
  return null;
}

// const AutoRules = () => {
// 
//   // initialize needed state
//   const [rules,setRules] = useState([]);
//   const [schedule,setSchedule] = useState({
//     1: ["00:00", "23:59"],
//     2: ["00:00", "23:59"],
//     3: ["00:00", "23:59"],
//     4: ["00:00", "23:59"],
//     5: ["00:00", "23:59"],
//     6: ["00:00", "23:59"],
//     7: ["00:00", "23:59"]
//   });
//   const [loaded, setLoaded] = useState(false);
// 
//   const onChangeRules = (newRules) => {
//     var newState = JSON.parse(JSON.stringify(rules));
// 
//     var value = _.without(newRules.trim().split("\n"), "");
//     _.set(newState, "rules",value);
// 
//     setRules();
//   }
// 
//   // handle state changes
//   useEffect(async () => {
//    if(!loaded) {
//      const autoRules = await fetchAutoRules();
//      setRules(autoRules.rules);
//      setSchedule(autoRules.schedule);
//      setLoaded(true);
//    }
//  }, [loaded]);
// }

export default class AutoRules extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
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
    }

    componentDidMount() {
        var that = this;
        fetch('/api/autorules', {method: 'get'}).then(function(response) {
            return response.json().then(function (json) {
                that.setState({
                  rules: json.rules,
                  schedule: json.schedule,
                  loaded: true
                });
            });
        }).catch(function(err) {
            // Error :(
        });
    }

    onChangeRules(rules) {
      var newState = JSON.parse(JSON.stringify(this.state.rules));

      var value = _.without(rules.trim().split("\n"), "");
      _.set(newState, "rules",value);

      this.setState({
          rules: newState.rules
      });
    }


    componentDidUpdate(prevProps, prevState) {

      if(prevState.loaded === false) {
        return;
      }

      var payload = new FormData();
      payload.append('rules', this.state.rules);

      // var data = new FormData();
      // data.append( "json", JSON.stringify( payload ) );

      fetch("/api/autorules/save",
      {
        method: "POST",
        body: JSON.stringify({rules: this.state.rules, schedule: this.state.schedule}),
        headers: new Headers({
	          'Content-Type': 'application/json'
        })
      })
      .then(function(res){ return res.json(); })
      .then(function(data){ console.log(data) });
    }

    onChangeSchedule(dayIndex,dayIndexKey,e) {
      var newState = JSON.parse(JSON.stringify(this.state.schedule));

      _.set(newState, dayIndex + ".[" + dayIndexKey + "]",e.target.value);

      this.setState({
          schedule: newState
      });
    }


    render() {
        var that = this;

        if(!this.state.loaded) {
          return <Loading />
        }

        var styles = {
          width: '100%'
        }

        var rules = "";

        if(typeof this.state.rules != 'undefined') {
            rules = this.state.rules.join("\n");
        }

        var styles = {
          width: "60px"
        }

        var spacer = {
          display: "inline-block",
          width: "80px"
        }

        var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"].map(function (row, idx) {
          return (
            <tr key={row}>
              <td>{row}</td>
              <td>
                <div className="row row-cols-md-auto g-3 align-items-center">
                  <div className="col-6">
                    <input
                      onChange={that.onChangeSchedule.bind(that, idx+1, 0)}
                      type="text"
                      className="form-control input-sm"
                      style={{width: "70px", marginRight: 0}}
                      defaultValue={that.state.schedule[idx+1][0]}
                      /></div>
                      <div className="col-6">
                    <input
                      onChange={that.onChangeSchedule.bind(that, idx+1, 1)}
                      type="text"
                      className="form-control input-sm"
                      style={{width: "70px"}}
                      defaultValue={that.state.schedule[idx+1][1]}
                      />
                    </div>
                  </div>
                </td>
            </tr>
          )
        });

        return (
            <div className="well">
              <div className="hbd">
                <h1 className="heading">Autotrading Rules</h1>
                <p className="description">Anything that matches rules on this page will be marked as approved, and possibly autotraded</p>
              </div>
              <div className="row">
                <div className="col-md-3 form-inline">
                  <p>Schedule:</p>
                <table className="table table-bordered">
                  <tbody>
                    {days}
                  </tbody>
                </table>

                </div>
                <div className="col-md-9">
                  <p>One rule per line</p>
                  <div className="form-group">
                      <RulesEditor keyRef="rules" defaultValue={rules} onChange={
                          this.onChangeRules.bind(this)
                      } />
                  </div>
                  <div className="alert alert-warning">You can use the special variable <code>[chain]</code> only on this page to write auto rules based on which sites are in the chain.</div>
                </div>
              </div>
            </div>
        )
    }
}
