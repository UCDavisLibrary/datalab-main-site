import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/hackathon-contact';
const settings = {
  api_version: 2,
	title: "Hackathon Contact Block",
	description: "Displays a contact block for a hackathon/data challenge",
	icon: UCDIcons.renderPublic('fa-address-book'),
	category: 'ucdlib-datalab',
	keywords: [ 'email', 'website', 'contact'],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
