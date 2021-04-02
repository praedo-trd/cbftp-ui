const esbuild = require("esbuild");
const sassPlugin = require("esbuild-plugin-sass");

const watchMode = process.env.ESBUILD_WATCH === "true" || false;
if (watchMode) {
  console.log("Running in watch mode...\n");
}
else {
  console.log("Creating production build...");
}

const watchModeObject = {
  onRebuild: (error, result) => {
    if (error) console.error("Build failure:", error);
    else console.error("Build successful");
  },
};

esbuild
  .build({
    entryPoints: ["./web/frontend/js/react/jsx/TRD.jsx"],
    bundle: true,
    minify: true,
    sourcemap: true,
    watch: watchMode ? watchModeObject : false,
    platform: "browser",
    outfile: "./web/frontend/js/dist/bundle.esbuild.js",
    define: { "process.env.NODE_ENV": '"production"' },
    target: ["es2020"],
    plugins: [sassPlugin()],
    loader: { ".woff": "file" },
  })
  .then((result) => {
    console.log("Build successful");
  })
  .catch((e) => {
    console.log(e);
    process.exit(1);
  });
