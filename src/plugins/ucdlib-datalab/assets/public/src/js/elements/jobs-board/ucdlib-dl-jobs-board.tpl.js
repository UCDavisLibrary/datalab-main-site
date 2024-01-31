import { html, css, svg } from 'lit';
import '@ucd-lib/theme-elements/brand/ucd-theme-pagination/ucd-theme-pagination.js'

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import baseHtml from "@ucd-lib/theme-sass/1_base_html/_index.css.js";
import baseClass from "@ucd-lib/theme-sass/2_base_class/_index.css.js";
import brandTextBox from "@ucd-lib/theme-sass/4_component/_brand-textbox.css.js";
import categoryBrand from "@ucd-lib/theme-sass/4_component/_category-brand.css.js";
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
    .job .job__title {
      font-size: 1rem;
      color: var(--ucd-blue, #022851);
    }
    .job .job__details {
      font-size: 0.8em;
    }
    .job__details .label {
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
        <ul class='list--arrow'>
          ${this.jobs.map(job => html`
            <li>
              <div class='job'>
                <h3 class='job__title'>${job.title}${job.employer ? ` - ${job.employer}` : ''}</h3>
                <div class='job__details'>
                  <div>
                    <span class='label'>Posted: </span>
                    ${job.posted}
                  </div>
                  <div>
                    <span class='label'>Closes: </span>
                    ${job.endDate}
                  </div>
                  <div ?hidden=${!job.additionalFields.length}>
                    <a class='pointer' @click=${() => this._onJobDetailsToggle(job.id)}>${this.expandedJobs.includes(job.id) ? 'Hide Details' : 'Show Details'}</a>
                  </div>
                  <div ?hidden=${!this.expandedJobs.includes(job.id)}>
                    ${job.additionalFields.map(field => renderAdditionalField.call(this, field))}
                  </div>
                </div>
              </div>
            </li>
          `)}
        </ul>
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
