import { Fragment } from "@wordpress/element";
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import {
  CheckboxControl,
  DatePicker,
  Dropdown,
  FormTokenField,
  SelectControl,
  TextControl,
  TextareaControl,
  __experimentalText as Text,
  ToggleControl,
  Modal,
  Button } from '@wordpress/components';

import { useEffect, useState } from '@wordpress/element';
import { useDispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';

import { html, SelectUtils } from "@ucd-lib/brand-theme-editor/lib/utils";

const name = 'ucdlib-datalab-hackathon-settings';


const Edit = () => {

  const isHackathon = SelectUtils.editedPostAttribute('type') === 'hackathon';
  if ( !isHackathon )  return html`<${Fragment} />`;

  const {editEntityRecord} = useDispatch( 'core' );

  // modal state
  const [ modalIsOpen, setModalOpen ] = useState( false );
  const openModal = () => {
    setModalOpen( true )
  };
  const closeModal = () => setModalOpen( false );

  // set up component state - set default to current post meta
  const postMeta = SelectUtils.editedPostAttribute('meta');
  const watchedVars = [postMeta.landingPageTitle];
  const { editPost } = useDispatch( 'core/editor', watchedVars );
  const [ landingPageTitle, setLandingPageTitle ] = useState( postMeta.landingPageTitle || 'Challenge Overview' );

  // set component state from current page meta
  const setStateFromCurrentPage = () => {
    setLandingPageId( 0 ); // the current page is the landing page for this hackathon
    setLandingPageTitle( postMeta.landingPageTitle );
  };

  const parent = SelectUtils.editedPostAttribute('parent') || 0;
  const [ parentError, setParentError ] = useState( false );
  const [ landingPageId, setLandingPageId ] = useState( 0 );

  // save component state variables to either the current page or hackathon landing page
  const saveMetadata = () => {
    const data = {
      meta: {
        landingPageTitle
      }
    };

    if ( landingPageId ){
      editEntityRecord('postType', 'hackathon', landingPageId, data);
    } else {
      editPost(data);
    }
    closeModal();

  };

  return html`
    <${PluginPostStatusInfo}
      className=${name}>
      <div>
        <${Button} onClick=${openModal} variant="primary">Edit Hackathon Metadata</${Button}>
        ${modalIsOpen && html`
          <${Modal} title='Hackathon Metadata' onRequestClose=${closeModal} shouldCloseOnClickOutside=${false}>
          ${parentError ? html`
            <div><p>There was an error when retrieving exhibit metadata. Please try again later.</p></div>
            ` : html`
            <${TextControl}
              label='Landing Page Title'
              value=${landingPageTitle}
              onChange=${(v) => setLandingPageTitle(v)}
            />
            `}


            <div style=${{marginTop: '20px', marginBottom: '10px'}}>
              <${Button} onClick=${saveMetadata} variant="primary">Save</${Button}>
              <${Button} onClick=${closeModal} variant="secondary">Close</${Button}>
            </div>
            ${landingPageId != 0 && html`
              <${Text} isBlock variant='muted'>After saving changes, you must still 'Update' this page for your changes to take effect.</${Text}>
            `}
          </${Modal}>
        `}
      </div>
    </${PluginPostStatusInfo}>
  `;
};


const settings = {render: Edit};
export default { name, settings };
