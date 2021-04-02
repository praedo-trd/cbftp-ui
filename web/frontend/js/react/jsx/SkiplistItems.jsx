import React from "react";

export default class SkiplistItems extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
        };
    }

    render() {
        var that = this;
        var rows = this.props.items.join("\n");

        var styles = {
          width: '100%'
        }

        return (
            <textarea className="form-control" style={styles} rows={10} defaultValue={rows} onChange={this.props.onChange.bind(this, "items", this.props.name)}></textarea>
        )
    }
}
