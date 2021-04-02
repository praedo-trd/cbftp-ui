import React from "react";

export default class SettingsEditorFormRow extends React.Component {
  
  constructor(props) {
    super(props);
  }
  
  render() {
    return (
        <div className="row mb-3">
          <label className="col-sm-3 col-form-label pt-0">{this.props.label}</label>

          <div className="col-sm-9">
            {this.props.children}
          </div>
        </div>
    )
  }
}  
