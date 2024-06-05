import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/hackathons';
const settings = {
  api_version: 2,
	title: "Past Hackathon Search Block",
	description: "A block for searching and displaying hackathon/data challenge",
	icon: UCDIcons.renderPublic('fa-magnifying-glass'),
	category: 'ucdlib-datalab',
	keywords: [ 'hackathon', 'data challenge', 'search'],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
