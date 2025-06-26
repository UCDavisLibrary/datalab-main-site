import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Fragment, useEffect } from "@wordpress/element";
import { useDispatch } from "@wordpress/data";
import {
  Button,
  DatePicker,
  Dropdown,
  SelectControl,
  ToggleControl,
 } from '@wordpress/components';
import { html, SelectUtils } from "@ucd-lib/brand-theme-editor/lib/utils";

const name = 'ucdlib-datalab-project-settings';

const Edit = () => {

  // bail if not project
  const isProject = SelectUtils.editedPostAttribute('type') === 'project';
  if ( !isProject )  return html`<${Fragment} />`;

  // watch changes to custom metadata
  const meta = SelectUtils.editedPostAttribute('meta');
  const projectStatus = meta.projectStatus || 'active';
  const projectStartDate = meta.projectStartDate || '';
  const projectEndDate = meta.projectEndDate || '';
  const showLink = meta.showLink || false;
  const watchedVars = [
      projectStatus,
      projectStartDate,
      projectEndDate,
      showLink
  ];
  const { editPost } = useDispatch( 'core/editor', watchedVars );

  // date formated as YYYY-MM-DD
  const updateStatusFromEndDate = (date) => {
    if ( !date ){
      editPost({meta: {projectStatus: 'active'}});
      return;
    }
    const today = new Date();
    const d = new Date(date);
    if ( d > today ) {
      editPost({meta: {projectStatus: 'active'}});
    } else {
      editPost({meta: {projectStatus: 'complete'}});
    }
  }

  // project range date picker
  const datePickerDropdown = (onDropdownClose, field) => {
    let value = field == 'projectStartDate' ? projectStartDate : projectEndDate;
    if ( value && value.length == 8) {
      value = `${value.slice(0,4)}-${value.slice(4,6)}-${value.slice(6,8)}T12:00:00Z`;
    } else if ( value && value.length == 10) {
      value = `${value}T12:00:00Z`;
    } else if (value) {
      console.warn(`${field} date was saved in incorrect format: ${value}`);
      value = null;
    }
    const onChange = (v) => {
      const d = v.split('T')[0];
      editPost({meta: {[field]: d}});
      if ( field === 'projectEndDate' ){
        updateStatusFromEndDate(d);
      }
      onDropdownClose();
    }
    const onReset = () => {
      editPost({meta: {[field]: null}});
      if ( field === 'projectEndDate' ){
        updateStatusFromEndDate(null);
      }
      onDropdownClose();
    }
    return html`
      <div>
        <${DatePicker} currentDate=${value} onChange=${onChange} />
        ${value && html`
          <${Button} variant='link' isDestructive=${true} onClick=${onReset}>Reset</${Button}>
        `}
      </div>
    `
  }
  const dateLabel = (d) => {
    if ( !d ) return 'Not Set';
    return d;
  }

  // get today formatted as YYYY-MM-DD - the way WP likes dates
  const getToday = () => {
    const today = new Date();
    const y = today.getFullYear();
    const m = (today.getMonth()+1).toString().padStart(2, '0');
    const d = today.getDate().toString().padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  // if project start date is not set, set it to today
  useEffect(() => {
    if ( projectStartDate ) return;
    const today = getToday();
    editPost({meta: {projectStartDate: today}});

  }, [projectStartDate]);

  const statusOptions = [
    {label: 'Active', value: 'active'},
    {label: 'Complete', value: 'complete'}
  ];

  // handle project status change
  const onStatusChange = (v) => {
    const meta = {projectStatus: v};

    if ( v == 'complete' && !projectEndDate ) {
      meta.projectEndDate = getToday();
    }

    editPost({meta});
  };

  return html`
    <${PluginDocumentSettingPanel}
      className=${name}
      icon=${html`<ucdlib-icon style=${{marginLeft: '8px', width: '15px', minWidth: '15px'}} icon="ucd-public:fa-folder-open"></ucdlib-icon>`}
      title='Project Settings'>
      <div style=${{marginBottom: '1rem'}}>
        <${SelectControl}
          options=${statusOptions}
          label='Project Status'
          value=${projectStatus}
          onChange=${onStatusChange}
        />
      </div>
      <div style=${{marginBottom: '1rem'}}>
        <${Dropdown}
          renderToggle=${({onToggle }) => html`
            <div onClick=${onToggle} style=${{cursor:'pointer'}}>
              <span>Project Start Date: </span>
              <span className='components-button is-link'>${dateLabel(projectStartDate)}</span>
            </div>
          `}
          renderContent=${({ onClose }) => datePickerDropdown(onClose, 'projectStartDate')}
        />
      </div>
      <div style=${{marginBottom: '1rem'}}>
        <${Dropdown}
          renderToggle=${({onToggle }) => html`
            <div onClick=${onToggle} style=${{cursor:'pointer'}}>
              <span>Project End Date: </span>
              <span className='components-button is-link'>${dateLabel(projectEndDate)}</span>
            </div>
          `}
          renderContent=${({ onClose }) => datePickerDropdown(onClose, 'projectEndDate')}
        />
      </div>
      <${ToggleControl}
        label='Show Link on Project Listing Page'
        checked=${showLink}
        onChange=${() => {editPost({meta: {showLink: !showLink}})}}
      />

    </${PluginDocumentSettingPanel}>
  `;
};

const settings = {render: Edit};
export default { name, settings };
