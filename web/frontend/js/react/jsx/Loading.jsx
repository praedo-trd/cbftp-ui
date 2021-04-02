import React from "react";

export default class Loading extends React.Component {
  render() {
    return (
      <button className="btn btn-primary" type="button" disabled>
        <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        {" "}Loading...
        </button>
    )
  }
}
