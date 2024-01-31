import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/projects';
const settings = {
  api_version: 2,
	title: "Datalab Projects",
	description: "A filterable list of datalab projects",
	icon: UCDIcons.renderPublic('fa-folder-tree'),
	category: 'ucdlib-datalab',
	keywords: [ 'project' ],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
