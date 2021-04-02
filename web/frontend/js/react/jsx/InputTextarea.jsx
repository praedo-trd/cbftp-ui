import React from "react";
import SettingsEditorFormRow from "./SettingsEditorFormRow";
import classNames from "classnames";

export default class InputTextarea extends React.Component {
    constructor(props) {
        super(props);
    }

    handleChange(changeEvent) {
        this.props.onChange(this.props.name, changeEvent);
    }

    render() {

        var classes = classNames('form-control', this.props.classes);

        var label = null;
        if(this.props.label) {
          return <SettingsEditorFormRow label={this.props.label}>
                  <textarea
                      name={this.props.name}
                      className={classes}
                      defaultValue={this.props.defaultValue}
                      onChange={this.handleChange.bind(this)}
                      rows={this.props.rows}
                  />
          </SettingsEditorFormRow>
        }

        return (
          <div className="form-group">
                  <textarea
                      name={this.props.name}
                      className={classes}
                      defaultValue={this.props.defaultValue}
                      onChange={this.handleChange.bind(this)}
                      rows={this.props.rows}
                  />
          </div>
        );
    }
}

InputTextarea.defaultProps = {
  rows: 5
};
