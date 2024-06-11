import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/hackathons-current';
const settings = {
  api_version: 2,
	title: "Current and Future Hackathons",
	description: "A block for searching and displaying current and future hackathon/data challenge",
	icon: UCDIcons.renderPublic('fa-code'),
	category: 'ucdlib-datalab',
	keywords: [ 'hackathon', 'data challenge'],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
