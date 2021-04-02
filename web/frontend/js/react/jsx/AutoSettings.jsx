import React from "react";
import _ from "lodash";

export default class AutoSettings extends React.Component {

    constructor(props) {
        super(props);

        this.classifications = [
            "Animation",
            "Award Show",
            "Documentary",
            "Game Show",
            "News",
            "Reality",
            "Scripted",
            "Sports",
            "Talk Show",
            "Variety"
        ];

        this.countries = [
          "United States", "Canada", "United Kingdom", "Australia", "New Zealand", "Denmark", "Norway", "Sweden"
        ];

        this.state = {
          setClassifications: [],
          setCountries: [],
          loaded: false
        };
    }

    componentDidMount() {
        var that = this;
        fetch('/api/auto/settings/' + this.props.section, {method: 'get'}).then(function(response) {
            return response.json().then(function (json) {
                that.setState({
                  setClassifications: json.allowed_classifications,
                  setCountries: json.allowed_countries,
                  loaded: true
                });
            });
        }).catch(function(err) {
            // Error :(
        });
    }

    onChangeAllowedClassifications(e) {
      var classifications = this.state.setClassifications || [];
      var classification = e.target.value;
      if(!e.target.checked) {
        _.remove(classifications, function (c) {
          return c === classification;
        });
      }
      else {
        classifications.push(classification);
        classifications.sort();
      }
      this.setState({
        setClassifications: classifications
      });
    }

    onChangeAllowedCountries(e) {
      var countries = this.state.setCountries || [];
      var country = e.target.value;
      if(!e.target.checked) {
        _.remove(countries, function (c) {
          return c === country;
        });
      }
      else {
        countries.push(country);
        countries.sort();
      }
      this.setState({
        setCountries: countries
      });
    }

    componentDidUpdate(prevProps, prevState) {

      if(prevState.loaded === false) {
        return;
      }

      var payload = new FormData();
      payload.append('allowed_classifications', this.state.setClassifications);
      payload.append('allowed_countries', this.state.setCountries);

      // var data = new FormData();
      // data.append( "json", JSON.stringify( payload ) );

      fetch("/api/auto/settings/" +  this.props.section,
      {
        method: "POST",
        body: payload
      })
      .then(function(res){ return res.json(); })
      .then(function(data){ console.log(data) });
    }

    render() {
        var that = this;
        return (
            <div className="well">
                <h3>Settings</h3>
                <div className="form-group">
                    <label className="control-label">Allowed classifications:</label><br />
                    {that.classifications.map(function (classification) {

                      var checked = false;
                      if(that.state.setClassifications.indexOf(classification) > -1) {
                        checked = true;
                      }

                      return (
                        <label className="checkbox-inline" key={classification}>
                          <input onClick={that.onChangeAllowedClassifications.bind(that)} type="checkbox" value={classification} checked={checked} /> {classification}
                        </label>
                      )
                    })}
                </div>
                <div className="form-group">
                    <label className="control-label">Allowed countries:</label><br />
                    {that.countries.map(function (country) {

                      var checked = false;
                      if(that.state.setCountries.indexOf(country) > -1) {
                        checked = true;
                      }

                      return (
                        <label className="checkbox-inline" key={country}>
                          <input onClick={that.onChangeAllowedCountries.bind(that)} type="checkbox" value={country} checked={checked} /> {country}
                        </label>
                      )
                    })}
                </div>
            </div>
        )
    }
}
