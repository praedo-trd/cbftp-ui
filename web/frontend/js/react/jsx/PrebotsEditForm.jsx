import React from "react";

const PrebotsEditForm = ({ data, index, onChange, onDelete }) => {
  const onChangeLocal = (field, e) => {
    onChange(index, field, e.currentTarget.value);
  };

  const onDeleteLocal = (e) => {
    const sure = confirm("You sure?");
    if (sure) {
      onDelete(index);
    }
  };

  return (
    <div>
      <div className="card">
        <div className="card-body">
          <div className="mb-3">
            <label className="form-label">Channel (regex)</label>
            <input
              className="form-control"
              type="text"
              value={data.channel}
              onChange={onChangeLocal.bind(this, "channel")}
            />
          </div>
          <div className="mb-3">
            <label className="form-label">Bot (regex)</label>
            <input
              className="form-control"
              type="text"
              value={data.bot}
              onChange={onChangeLocal.bind(this, "bot")}
            />
          </div>
          <div className="mb-3">
            <label className="form-label">String match (regex)</label>
            <input
              className="form-control"
              type="text"
              value={data.string_match}
              onChange={onChangeLocal.bind(this, "string_match")}
            />
            <div className="form-text">
              Make sure to use parentheses for matching the rlsname
            </div>
          </div>
          <div className="mb-3">
            <button
              className="btn btn-outline-secondary"
              onClick={onDeleteLocal}
            >
              Delete
            </button>
          </div>
        </div>
      </div>
      <br />
    </div>
  );
};

export default PrebotsEditForm;
