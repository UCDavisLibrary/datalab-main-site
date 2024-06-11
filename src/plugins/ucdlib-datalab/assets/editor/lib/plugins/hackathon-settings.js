import { Fragment } from "@wordpress/element";
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import {
  DateTimePicker,
  Dropdown,
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
import { ImagePicker } from "@ucd-lib/brand-theme-editor/lib/block-components";

const name = 'ucdlib-datalab-hackathon-settings';


const Edit = () => {

  const isHackathon = SelectUtils.editedPostAttribute('type') === 'hackathon';
  if ( !isHackathon )  return html`<${Fragment} />`;

  // type taxonomy terms
  const typeTerms = SelectUtils.terms('hackathon-type', {per_page: '-1', orderby: 'name', order: 'asc'});
  const typeOptions = [
    {value: '', label: 'Select a type', disabled: true},
    ...typeTerms.map(l => {return {value: l.id, label: l.name}})
  ];

  const {editEntityRecord} = useDispatch( 'core' );

  // modal state
  const [ modalIsOpen, setModalOpen ] = useState( false );
  const openModal = () => {
    setModalOpen( true )
  };
  const closeModal = () => setModalOpen( false );

  // set up component state - set default to current post meta
  const postMeta = SelectUtils.editedPostAttribute('meta');
  const postHackathonTypes = SelectUtils.editedPostAttribute('hackathon-type');
  const watchedVars = [
    postMeta.hackathonLandingPageTitle,
    postMeta.hackathonStartDate,
    postMeta.hackathonEndDate,
    postMeta.hackathonExcerpt,
    postMeta.hackathonHostedByExternal,
    postMeta.hackathonContactEmail,
    postMeta.hackathonContactUrl,
    postMeta.showGrandchildrenInNav,
    postMeta.ucd_thumbnail_1x1,
    postMeta.ucd_thumbnail_4x3,
    postHackathonTypes
  ];
  const { editPost } = useDispatch( 'core/editor', watchedVars );
  const [ hackathonLandingPageTitle, setHackathonLandingPageTitle ] = useState( postMeta.hackathonLandingPageTitle || 'Challenge Overview' );
  const [ hackathonStartDate, setHackathonStartDate ] = useState( postMeta.hackathonStartDate || '' );
  const [ hackathonEndDate, setHackathonEndDate ] = useState( postMeta.hackathonEndDate || '' );
  const [ hackathonExcerpt, setHackathonExcerpt ] = useState( postMeta.hackathonExcerpt || '' );
  const [ hackathonTypes, setHackathonTypes ] = useState( postHackathonTypes || [] );
  const [ hackathonHostedByExternal, setHackathonHostedByExternal ] = useState( postMeta.hackathonHostedByExternal || false );
  const [ hackathonContactEmail, setHackathonContactEmail ] = useState( postMeta.hackathonContactEmail || '' );
  const [ hackathonContactUrl, setHackathonContactUrl ] = useState( postMeta.hackathonContactUrl || '' );
  const [ showGrandchildrenInNav, setShowGrandchildrenInNav ] = useState( postMeta.showGrandchildrenInNav || false );
  const [ hackathonTeaserImage, setHackathonTeaserImage ] = useState( postMeta.ucd_thumbnail_1x1 || 0 );
  const [ hackathonCardImage, setHackathonCardImage ] = useState( postMeta.ucd_thumbnail_4x3 || 0 );

  const teaserImageObject = SelectUtils.image(hackathonTeaserImage);
  const cardImageObject = SelectUtils.image(hackathonCardImage);

  // set component state from current page meta
  const setStateFromCurrentPage = () => {
    setLandingPageId( 0 ); // the current page is the landing page for this hackathon
    setHackathonLandingPageTitle( postMeta.hackathonLandingPageTitle );
    setHackathonStartDate( postMeta.hackathonStartDate || '' );
    setHackathonEndDate( postMeta.hackathonEndDate || '' );
    setHackathonExcerpt( postMeta.hackathonExcerpt || '' );
    setHackathonHostedByExternal( postMeta.hackathonHostedByExternal || false);
    setHackathonContactEmail( postMeta.hackathonContactEmail || '' );
    setHackathonContactUrl( postMeta.hackathonContactUrl || '' );
    setShowGrandchildrenInNav( postMeta.showGrandchildrenInNav || false );
    setHackathonTypes( postHackathonTypes || [] );
    setHackathonTeaserImage( postMeta.ucd_thumbnail_1x1 || 0)
    setHackathonCardImage( postMeta.ucd_thumbnail_4x3 || 0)
  };

  // if this page has a parent, we need to find the landing page for this hackathon
  // and then set the component state from the metadata for that page
  const parent = SelectUtils.editedPostAttribute('parent') || 0;
  const [ parentError, setParentError ] = useState( false );
  const [ landingPageId, setLandingPageId ] = useState( 0 );
  useEffect(() => {
    if ( !parent ) {
      setParentError(false);
      setStateFromCurrentPage();
      return;
    }
    const path = `ucdlib-datalab/hackathon/page/${parent}`;
    apiFetch( {path} ).then(
      ( r ) => {
        setParentError(false);
        setLandingPageId( r.hackathonLandingPageId)
        setHackathonLandingPageTitle( r.hackathonLandingPageTitle );
        setHackathonStartDate( r.hackathonStartDate || '' );
        setHackathonEndDate( r.hackathonEndDate || '' );
        setHackathonExcerpt( r.hackathonExcerpt || '' );
        setHackathonHostedByExternal( r.hackathonHostedByExternal || false);
        setHackathonContactEmail( r.hackathonContactEmail || '' );
        setHackathonContactUrl( r.hackathonContactUrl || '' );
        setHackathonTypes( (r.hackathonTypes || []).map(t => t.id) );
        setShowGrandchildrenInNav( r.showGrandchildrenInNav || false );
        setHackathonTeaserImage( r.hackathonTeaserImageId || 0);
        setHackathonCardImage( r.hackathonCardImageId || 0);
      },
      (error) => {
        setParentError(true);
        setStateFromCurrentPage();
      });

  }, [parent]);

  SelectUtils.post( landingPageId, 'hackathon');

  // save component state variables to either the current page or hackathon landing page
  const saveMetadata = () => {
    const data = {
      'hackathon-type': hackathonTypes,
      meta: {
        hackathonLandingPageTitle,
        hackathonStartDate,
        hackathonEndDate,
        hackathonExcerpt,
        hackathonHostedByExternal,
        hackathonContactEmail,
        hackathonContactUrl,
        showGrandchildrenInNav,
        ucd_thumbnail_1x1: hackathonTeaserImage,
        ucd_thumbnail_4x3: hackathonCardImage
      }
    };

    if ( landingPageId ){
      editEntityRecord('postType', 'hackathon', landingPageId, data);
    } else {
      editPost(data);
    }
    closeModal();

  };

  // startdate and enddate picker
  const datePickerDropdown = (onDropdownClose, field) => {
    let value = field == 'hackathonStartDate' ? hackathonStartDate : hackathonEndDate;
    if ( value ) {
      value = `${value}T12:00:00`;
    }

    const onChange = (v) => {
      const d = v.split('T')[0];
      if ( field === 'hackathonStartDate' ) {
        setHackathonStartDate(d);
      } else {
        setHackathonEndDate(d);
      }
    }
    const onReset = () => {
      if ( field === 'hackathonStartDate' ) {
        setHackathonStartDate(null);
      } else {
        setHackathonEndDate(null);
      }
      onDropdownClose();
    }
    return html`
      <div>
        <${DateTimePicker}
          is12Hour={ true }
          onChange=${onChange}
          currentDate=${value}
        />
        <div style=${{display: 'flex', justifyContent: 'space-between', marginTop: '1rem'}}>
          ${value && html`
            <${Button} variant='link' isDestructive=${true} onClick=${onReset}>Reset</${Button}>
          `}
          <${Button} variant='link' onClick=${onDropdownClose}>Close</${Button}>
        </div>
      </div>
    `;
  }
  const dateLabel = (d) => {
    if ( !d ) return 'Not Set';
    return d;
  }

  return html`
    <${PluginPostStatusInfo}
      className=${name}>
      <div>
        <style>
          .components-datetime__time fieldset:first-child {
            display: none !important;
          }
        </style>
        <${Button} onClick=${openModal} variant="primary">Edit Hackathon Metadata</${Button}>
        ${modalIsOpen && html`
          <${Modal} title='Hackathon Metadata' onRequestClose=${closeModal} shouldCloseOnClickOutside=${false}>
          ${parentError ? html`
            <div><p>There was an error when retrieving exhibit metadata. Please try again later.</p></div>
            ` : html`
              <${TextControl}
                label='Landing Page Title'
                value=${hackathonLandingPageTitle}
                onChange=${(v) => setHackathonLandingPageTitle(v)}
              />
              <${SelectControl}
                label='Type'
                options=${typeOptions}
                value=${hackathonTypes.length ? hackathonTypes[0] : ''}
                onChange=${id => setHackathonTypes([id])}
              />
              <div style=${{marginBottom: '1rem'}}>
                <${Dropdown}
                  renderToggle=${({onToggle }) => html`
                    <div onClick=${onToggle} style=${{cursor:'pointer'}}>
                      <span>Start Date: </span>
                      <span className='components-button is-link'>${dateLabel(hackathonStartDate)}</span>
                    </div>
                  `}
                  renderContent=${({ onClose }) => datePickerDropdown(onClose, 'hackathonStartDate')}
                />
              </div>
              <div style=${{marginBottom: '1rem'}}>
                <${Dropdown}
                  renderToggle=${({onToggle }) => html`
                    <div onClick=${onToggle} style=${{cursor:'pointer'}}>
                      <span>End Date: </span>
                      <span className='components-button is-link'>${dateLabel(hackathonEndDate)}</span>
                    </div>
                  `}
                  renderContent=${({ onClose }) => datePickerDropdown(onClose, 'hackathonEndDate')}
                />
              </div>
              <${TextareaControl}
                label="Excerpt"
                value=${hackathonExcerpt}
                onChange=${(v) => setHackathonExcerpt(v)}
              />
              <div style=${{marginTop: '1rem'}}>
                <${ToggleControl}
                  label='Hosted by External Organization'
                  checked=${hackathonHostedByExternal}
                  onChange=${() => setHackathonHostedByExternal(!hackathonHostedByExternal)}
                />
              </div>
              ${hackathonHostedByExternal && html`
                <div>
                  <${TextControl}
                    label='Contact Email'
                    value=${hackathonContactEmail}
                    onChange=${(v) => setHackathonContactEmail(v)}
                  />
                  <${TextControl}
                    label='Contact URL'
                    value=${hackathonContactUrl}
                    onChange=${(v) => setHackathonContactUrl(v)}
                  />
                </div>
              `}
              <div style=${{marginTop: '1rem', marginBottom: '1rem'}}>
                <h3>Images</h3>
                <h4>Teaser Image</h4>
                <${ImagePicker}
                  imageId=${hackathonTeaserImage}
                  image=${teaserImageObject}
                  onSelect=${(image) => setHackathonTeaserImage(image.id)}
                  onRemove=${() => setHackathonTeaserImage(0)}
                  notPanel=${true}
                />
                <h4>Card Image</h4>
                <${ImagePicker}
                  imageId=${hackathonCardImage}
                  image=${cardImageObject}
                  onSelect=${(image) => setHackathonCardImage(image.id)}
                  onRemove=${() => setHackathonCardImage(0)}
                  notPanel=${true}
                />
              </div>

              <div style=${{marginTop: '1rem'}}>
                <${ToggleControl}
                  label='Show grandchildren pages in navigation'
                  checked=${showGrandchildrenInNav}
                  onChange=${() => setShowGrandchildrenInNav(!showGrandchildrenInNav)}
                />
              </div>
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
