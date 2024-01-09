import { UCDIcons } from "@ucd-lib/brand-theme-editor/lib/utils";
import Edit from './edit';

const name = 'ucdlib-datalab/jobs-board';
const settings = {
  api_version: 2,
	title: "Datalab Jobs Board",
	description: "Displays interactive jobs board.",
	icon: UCDIcons.renderPublic('fa-user-tie'),
	category: 'ucdlib-datalab',
	keywords: [ 'job' ],
  supports: {
    "html": false,
    "customClassName": false
  },
  attributes: {
  },
  edit: Edit
};

export default { name, settings };
