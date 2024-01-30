import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/project-partners';
const settings = {
  api_version: 2,
	title: "Datalab Project Partners",
	description: "Displays partners for a project.",
	icon: UCDIcons.renderPublic('fa-handshake'),
	category: 'ucdlib-datalab',
	keywords: [ 'partner', 'project' ],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
