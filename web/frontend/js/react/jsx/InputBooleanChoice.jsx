import React from "react";
import SettingsEditorFormRow from "./SettingsEditorFormRow";

export default class InputBooleanChoice extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            selectedOption: this.props.defaultValue
        };
    }

    handleOptionChange(changeEvent) {
        var val = changeEvent.target.value == 'true';
        this.setState({
            selectedOption: val
        });
        this.props.onChange(this.props.name, changeEvent);
    }

    render() {


        return (
          <>
          <SettingsEditorFormRow label={this.props.label}>
            <div className="form-check form-check-inline">
              <input
                  name={this.props.name}
                  id={`booleanChoiceTrue${this.props.name}`}
                  type="radio"
                  value="true"
                  checked={this.state.selectedOption == true}
                  onChange={this.handleOptionChange.bind(this)}
                  className="form-check-input"
              />
              <label className="check-label" htmlFor={`booleanChoiceTrue${this.props.name}`}> True</label>
            </div>
            <div className="form-check form-check-inline">
              <input
                  name={this.props.name}
                  id={`booleanChoiceFalse${this.props.name}`}
                  type="radio"
                  value="false"
                  checked={this.state.selectedOption == false}
                  onChange={this.handleOptionChange.bind(this)}
                  className="form-check-input"
              />
              <label className="form-check-label" htmlFor={`booleanChoiceFalse${this.props.name}`}> False</label>
            </div>
            </SettingsEditorFormRow>
          </>
        );
    }
}
