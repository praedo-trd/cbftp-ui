import React from "react";
import InputText from "./InputText";
import InputTextarea from "./InputTextarea"

export default class InputTagOption extends React.Component {
    constructor(props) {
        super(props);
    }

    handleChange(changeEvent) {
        this.props.onChange(this.props.name, changeEvent);
    }

    onChangeSetting(key, event) {
      this.props.onChange("tag_options." + this.props.tag + "." + key, event);
    }

    render() {

        let data_sources = [];
        if(this.props.info.data_sources) {
          data_sources = this.props.info.data_sources
              .sort()
              .join(" ");
        }

        let allowed_groups = [];
        if(this.props.info.allowed_groups) {
          allowed_groups = this.props.info.allowed_groups
              .sort()
              .join(" ");
        }

        let tag_requires = [];
        if(this.props.info.tag_requires) {
          tag_requires = this.props.info.tag_requires
              .sort()
              .join("\n");
        }

        let tag_skiplist = [];
        if(this.props.info.tag_skiplist) {
          tag_skiplist = this.props.info.tag_skiplist
              .sort()
              .join("\n");
        }

        return (
            <div>
              <InputText
                  name="data_sources"
                  label="Data providers (space-seperator)"
                  description="Bla bla"
                  defaultValue={data_sources}
                  onChange={this.onChangeSetting.bind(this)}
              />
            <InputText
                  name="allowed_groups"
                  label="Allowed groups (space-seperator)"
                  description="Bla bla"
                  defaultValue={allowed_groups}
                  onChange={this.onChangeSetting.bind(this)}
              />
              <InputTextarea
                  name="tag_requires"
                  label="Tag requires (one regex p/line)"
                  description="Bla bla"
                  defaultValue={tag_requires}
                  onChange={this.onChangeSetting.bind(this)}
                  rows={10}
                  classes="code"
              />
              <InputTextarea
                  name="tag_skiplist"
                  label="Tag skiplist (one regex p/line)"
                  description="Bla bla"
                  defaultValue={tag_skiplist}
                  onChange={this.onChangeSetting.bind(this)}
                  rows={10}
                  classes="code"
              />
            </div>
        );
    }
}
