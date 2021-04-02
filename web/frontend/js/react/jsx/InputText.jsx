import React from "react";
import SettingsEditorFormRow from "./SettingsEditorFormRow";
import classNames from "classnames";

export default class InputText extends React.Component {
    constructor(props) {
        super(props);
    }

    handleChange(changeEvent) {
        this.props.onChange(this.props.name, changeEvent);
    }

    render() {

        var classes = classNames('form-control', this.props.classes);

        return (
            <SettingsEditorFormRow label={this.props.label}>
                <input
                    name={this.props.name}
                    type="text"
                    className={classes}
                    defaultValue={this.props.defaultValue}
                    onChange={this.handleChange.bind(this)}
                />
            </SettingsEditorFormRow>
        );
    }
}
