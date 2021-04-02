import React from "react";
import ReactDOM from 'react-dom';
import AceEditor from "react-ace";

import 'ace-builds/src-noconflict/ext-language_tools';
import "ace-builds/src-noconflict/theme-monokai";
import "../../misc/ace/mode/mode-trd";

const providerWordList = [];

// ReleaseNameDataProvider
[
  "cleaned", "group", "episode", "season", "codec"
  , "resolution", "language", "multi", "internal",
  "source", "range", "repeat"
].forEach(field => {
  providerWordList.push(`[rlsname.${field}]`);
});

// TVMazeDataProvider
[
  "country", "country_code", "classification", "genres", "language",
  "daily", "network", "status", "runtime", "premiered", "year",
  "total_seasons", "latest_season", "current_season", "last_season",
  "aired_in_last_6_months", "recent_seasons", "is_scripted_english"
].forEach(field => {
  providerWordList.push(`[tvmaze.${field}]`);
});

// IMDBDataProvider
[
  "genres", "language_primary", "language", "languages",
  "country", "countries", "votes", "stv",
  "series", "rating", "runtime", "title", "year"
].forEach(field => {
  providerWordList.push(`[imdb.${field}]`);
});

// MusicDataProvider
[
  "language", "source", "year", "disk_count"
].forEach(field => {
  providerWordList.push(`[music.${field}]`);
});

[
  "screens_us", "screens_uk", "has_screens", "limited", "wide"
].forEach(field => {
  providerWordList.push(`[bom.${field}]`);
});

const endTokens = ["ALLOW", "DROP", "EXCEPT"];
const operators = ["iswm", "isin", "contains", "containsany", "matches"];


const customCompleter = {
  //identifierRegexps: [/[\.]/],
  getCompletions: (editor, session, pos, prefix, cb) => {
    var completions = [];
    // we can use session and pos here to decide what we are going to show
    providerWordList.forEach(function(w) {
        completions.push({
            value: w.slice(1) + " ",
            meta: "Data provider",
            caption: w

        });
    });
    operators.forEach(function(w) {
        completions.push({
            value: w + " ",
            meta: "Operator",

        });
    });
    endTokens.forEach(function(w) {
        completions.push({
            value: w,
            meta: "Terminator",

        });
    });
    cb(null, completions);
  }
}

export default class RulesEditor extends React.Component {
  
  constructor(props) {
    super(props);
    this.myEditor = React.createRef();
  }
  
  componentDidMount() {
    const editor = this.myEditor.current.editor;
		editor.getSession().setMode("ace/mode/trd");
    // editor.commands.addCommand({
    //     name: "dotCommand1",
    //     bindKey: { win: ".", mac: "." },
    //     exec: function () {
    //         var pos = editor.selection.getCursor();
    //         var session = editor.session;
    // 
    //         var curLine = (session.getDocument().getLine(pos.row)).trim();
    //         var curTokens = curLine.slice(0, pos.column).split(/\s+/);
    //         var curCmd = curTokens[0];
    //         if (!curCmd) return;
    //         var lastToken = curTokens[curTokens.length - 1];
    // 
    //         editor.insert(".");                
    // 
    //         if (lastToken === "foo") {
    //             // Add your words to the list or then insert into the editor using editor.insert()
    //             //wordList = ["bar"]
    //         }
    //     }
    //   })
	}
  
  componentDidUpdate(prevProps, prevState, snapshot) {
    if(prevProps.keyRef != this.props.keyRef) {
      this.myEditor.current.editor.getSession().setValue(this.props.defaultValue);
    }
  }
  
  render() {
    return <AceEditor
            ref={this.myEditor}
            mode="text"
            theme="monokai"
            width={this.props.width}
            defaultValue={this.props.defaultValue}
            onChange={this.props.onChange}
            showGutter={this.props.showGutter}
            showPrintMargin={this.props.showPrintMargin}
            setOptions={{
              enableBasicAutocompletion: [customCompleter],
              enableLiveAutocompletion: this.props.autocomplete,
            }}
            editorProps={{ 
              $blockScrolling: Infinity,
              //$fontSize: 5
            }}
          />
  }
}

// export default class RulesEditor extends React.Component {
// 
//   constructor(props) {
//     super(props);
// 
//     this._onChange = this.onChange.bind(this);
// 
//     this.state = {
//       changes: 0
//     };
//   }
// 
//   componentDidMount() {
// 
//     // console.log("Remounted");
//     // 
//     //   ace.config.set("modePath", "/frontend/js/misc/ace/mode/");
//     //   this.editor = ace.edit(this.props.name);
//     //   this.editor.$blockScrolling = Infinity;
//     //   this.editor.getSession().setMode('ace/mode/' + this.props.mode);
//     //   this.editor.setTheme('ace/theme/' + this.props.theme);
//     //   this.editor.on('change', this.onChange.bind(this));      
//     //   this.editor.setValue(this.props.defaultValue || this.props.value, (this.props.selectFirstLine === true ? -1 : null));
//     //   this.editor.setOption('maxLines', this.props.maxLines);
//     //   this.editor.setOption('readOnly', this.props.readOnly);
//     //   this.editor.setOption('highlightActiveLine', this.props.highlightActiveLine);
//     //   this.editor.setShowPrintMargin(this.props.setShowPrintMargin);
//     //   this.editor.getSession().setUseWrapMode(this.props.wrapEnabled);
//     //   this.editor.renderer.setShowGutter(this.props.showGutter);
//     // 
//     //   if (this.props.onLoad) {
//     //       this.props.onLoad(this.editor);
//     //   }
//   }
// 
//   UNSAFE_componentWillReceiveProps(nextProps) {
//         let currentRange = this.editor.selection.getRange();
// 
//         // if (nextProps.value && this.editor.getValue() !== nextProps.value) {
//         //     this.editor.setValue(nextProps.value, (this.props.selectFirstLine === true ? -1 : null));
//         //     if(currentRange && typeof currentRange === "object") {
//         //         this.editor.getSession().getSelection().setSelectionRange(currentRange);
//         //     }
//         // }
//         if(nextProps.keyRef !== this.props.keyRef) {
//           //console.log(this.props, nextProps);
//           this.editor.setValue(nextProps.defaultValue);
//           console.log("About to force update with new value", nextProps.defaultValue);
//           this.forceUpdate();
//         }
// 
//     }
// 
//   onChange() {
//       if(this.state.changes > 0) {
//         const value = this.editor.getValue();
//         this.props.onChange(value);
//       }
//       this.setState({
//         changes: this.state.changes+1
//       });
//   }
// 
//   render() {
//     const divStyle = {
//       width: this.props.width,
//       height: this.props.height,
//     };
// 
//     // return ReactDOM.div({
//     //   id: this.props.name,
//     //   onChange: this._onChange,
//     //   style: divStyle,
//     // });
// 
//     return (
//       <div id={this.props.name} onChange={this._onChange} style={divStyle}></div>
//     )
//   }
// }
// 
RulesEditor.defaultProps = {
  id: "editor",
  keyRef: "editor",
  name: "editor",
  width: "100%",
  height: "400px",
  mode: "trd",
  theme: "monokai",
  defaultValue: '',
  value: '',
  fontSize: 12,
  showGutter: true,
  onChange: null,
  onLoad: null,
  maxLines: null,
  readOnly: false,
  highlightActiveLine: true,
  showPrintMargin: false,
  selectFirstLine: false,
  wrapEnabled: false,
  autocomplete: false
}
