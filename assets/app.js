import "./bootstrap.js";
import "./styles/app.scss";
import "./styles/themes/theme-blue.scss";
import "./styles/themes/theme-green.scss";
import "./styles/themes/theme-pink.scss";
import "./styles/themes/theme-red.scss";
import "./styles/themes/theme-yellow.scss";
import { registerReactControllerComponents } from "@symfony/ux-react";

registerReactControllerComponents(require.context("./react/controllers", true, /\.(j|t)sx?$/));