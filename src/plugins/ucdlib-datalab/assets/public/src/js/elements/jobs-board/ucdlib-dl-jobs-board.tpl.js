import { html, css, svg } from 'lit';
import '@ucd-lib/theme-elements/brand/ucd-theme-pagination/ucd-theme-pagination.js'

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import baseHtml from "@ucd-lib/theme-sass/1_base_html/_index.css.js";
import baseClass from "@ucd-lib/theme-sass/2_base_class/_index.css.js";
import brandTextBox from "@ucd-lib/theme-sass/4_component/_brand-textbox.css.js";
import categoryBrand from "@ucd-lib/theme-sass/4_component/_category-brand.css.js";
import spaceUtils from "@ucd-lib/theme-sass/6_utility/_u-space.css.js";

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

    .loading {
        display: flex;
        justify-content: center;
    }
    .loading .loading-icon {
      animation: spin 2s linear infinite, opacity-pulse 2s linear infinite;
      width: calc(3vw);
      height: calc(3vw);
      max-width: 50px;
      max-height: 50px;
      min-width: 20px;
      min-height: 20px;
      color: #022851;
    }
    .loading-icon svg {
      display: block;
      width: 100%;
      height: 100%;
      fill: currentColor;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(359deg); }
    }
    @keyframes opacity-pulse {
      0% { opacity: 0.4; }
      25% { opacity: 0.5; }
      50% { opacity: 1; }
      75% { opacity: 0.5; }
      100% { opacity: 0.4; }
    }
  `;

  return [
    normalize,
    baseHtml,
    baseClass,
    brandTextBox,
    categoryBrand,
    spaceUtils,
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
    <div class='loading' style='height:${this.loadingHeight}'>
      <div class='loading-icon'>
      ${svg`
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
          <path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z"/>
        </svg>`}
      </div>
    </div>
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
