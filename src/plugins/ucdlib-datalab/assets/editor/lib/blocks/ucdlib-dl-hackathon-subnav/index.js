import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/hackathon-subnav';
const settings = {
  api_version: 2,
	title: "Hackathon Subnav",
	description: "Displays a subnav for a hierarchical hackathon/data challenge",
	icon: UCDIcons.renderPublic('fa-folder-tree'),
	category: 'ucdlib-datalab',
	keywords: [ 'menu', 'child'],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
