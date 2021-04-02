import React from "react";

export default class SkiplistItemRegex extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
        };
    }

    render() {
        var that = this;

        return (
            <textarea type="text" className="form-control" rows={10} defaultValue={this.props.regex} onChange={this.props.onChange.bind(this, "regex", this.props.name)}></textarea>
        )
    }
}
