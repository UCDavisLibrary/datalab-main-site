import { html, css, svg } from 'lit';
import '@ucd-lib/theme-elements/brand/ucd-theme-pagination/ucd-theme-pagination.js'

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import baseHtml from "@ucd-lib/theme-sass/1_base_html/_index.css.js";
import baseClass from "@ucd-lib/theme-sass/2_base_class/_index.css.js";
import brandTextBox from "@ucd-lib/theme-sass/4_component/_brand-textbox.css.js";
import categoryBrand from "@ucd-lib/theme-sass/4_component/_category-brand.css.js";
import l2col from "@ucd-lib/theme-sass/5_layout/_l-2col.css.js";
import l3col from "@ucd-lib/theme-sass/5_layout/_l-3col.css.js";
import gridRegions from "@ucd-lib/theme-sass/5_layout/_l-grid-regions.css.js";
import spaceUtils from "@ucd-lib/theme-sass/6_utility/_u-space.css.js";

import ElementStatusController from "../../controllers/element-status.js";

export function styles() {
  const elementStyles = css`
    :host {
      display: block;
    }
    [hidden] {
      display: none !important;
    }
    .job {
      margin-bottom: 1rem;
    }
    .job .l-second {
      margin-top: 0 !important;
      font-size: 0.875rem;
      color: #191919;
      border-left: #B0D0ED 1px solid;
      padding: .5rem 0 .5rem 1rem;
      justify-content: center;
      display: flex;
      flex-flow: column;
    }
    .job__title a {
      text-decoration: none;
      color: inherit;
    }
    .job__title a:hover {
      text-decoration: underline;
    }
    .job__below-title {
      font-size: 0.875rem;
      color: #4C4C4C;
      font-weight: 700;
    }
    .job__tags {
      display: flex;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    .job__tag {
      background-color: #DBEAF7;
      color: #022851;
      padding: 0.1rem .5rem;
      font-size: 0.875rem;
      font-weight: 700;
    }
    .pointer {
      cursor: pointer;
    }
    .header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
    }
    .no-jobs {
      display: flex;
      justify-content: center;
    }
  `;

  return [
    normalize,
    baseHtml,
    baseClass,
    brandTextBox,
    categoryBrand,
    l2col,
    l3col,
    gridRegions,
    spaceUtils,
    ElementStatusController.styles,
    elementStyles
  ];
}

// main render function for element
export function render() {
  if ( this.fetchStatus === 'loading' ) {
    return renderLoading.call(this);
  }
  if ( this.fetchStatus === 'loaded' ) {
    return renderLoaded.call(this);
  }
  return renderError.call(this);
}

function renderLoaded(){
  return html`
    ${renderHeader.call(this)}
    <div id='loaded'>
      <div ?hidden=${!this.jobs.length}>
        ${this.jobs.map(job => renderJobListing.call(this, job))}
      </div>
      <div ?hidden=${this.jobs.length} class='no-jobs'>
        <section class='brand-textbox category-brand__background'>
          No jobs found!
        </section>
      </div>
    </div>
    ${renderFooter.call(this)}
    `
}

function renderAdditionalField(field){
  let v = html`<span>${field.value}</span>`
  if ( field.type === 'url' ) {
    v = html`<a href="${field.value}">${field.value}</a>`
  }
  return html`
    <div>
      <span class='label'>${field.label}: </span>
      ${v}
    </div>
  `;
}

function renderJobListing(job){
  return html`
  <div class='job'>
    <div class='job__tags' ?hidden=${!job.positionType}><div class='job__tag'>${job.positionType}</div></div>
    <div class='job__main-content l-2col l-2col--67-33'>
      <div class='l-first'>
        <div class='job__title'>
          ${job.listingUrl ? html`
            <a href="${job.listingUrl}"><h5>${job.title}</h5></a>
          ` : html`
            <h5>${job.title}</h5>
          `}
        </div>
        <div class='job__below-title'>
          <div ?hidden=${!job.employer}>${job.employer}</div>
          <div ?hidden=${!job.location}>${job.location}</div>
        </div>
      </div>
      <div class='l-second'>
        <div ?hidden=${!job.endDate}><span>Apply by: </span><span>${job.endDate}</span></div>
        <div ?hidden=${!job.sector}><span>Sector: </span><span>${job.sector}</span></div>
        <div ?hidden=${!job.education}><span>Level: </span><span>${job.education}</span></div>
      </div>
    </div>
  </div>
  `;
}

function renderLoading(){
  return html`
    ${renderHeader.call(this)}
    ${this.statusController.renderLoading()}
    ${renderFooter.call(this)}
    `
}

function renderError(){
  return html`
    <section class='brand-textbox category-brand__background category-brand--double-decker'>
      An error occurred when loading the jobs board. Please try again later.
    </section>
    `
}

function renderHeader(){
  const inputsDisabled = this.fetchStatus !== 'loaded';
  return html`
    <div class='header'>
      <form @submit=${this._onSearchSubmit}>
        <div class="field-container">
          <input
            id="search"
            type="search"
            .value=${this.searchText}
            @input=${this._onSearchInput}
            ?disabled=${inputsDisabled}
            placeholder="Search all jobs...">
        </div>
      </form>
    </div>
  `;
}

function renderFooter(){
  if ( !this.totalPages || !this.currentPage ) return html``;
  return html`
  <ucd-theme-pagination
    current-page="${this.currentPage}"
    ellipses
    xs-screen
    @page-change=${this._onPageChange}
    max-pages=${this.totalPages}>
  </ucd-theme-pagination>
  `;
}
