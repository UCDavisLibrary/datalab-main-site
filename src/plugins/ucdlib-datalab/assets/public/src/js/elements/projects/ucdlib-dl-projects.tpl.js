import { html, css } from 'lit';
import '@ucd-lib/theme-elements/brand/ucd-theme-pagination/ucd-theme-pagination.js'

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import headings from "@ucd-lib/theme-sass/1_base_html/_headings.css.js";
import formStyles from "@ucd-lib/theme-sass/1_base_html/_forms.css.js";
import formsClasses from "@ucd-lib/theme-sass/2_base_class/_forms.css.js";
import icons from "@ucd-lib/theme-sass/4_component/_icons.css.js";
import l3col from "@ucd-lib/theme-sass/5_layout/_l-3col.css.js";
import lgridRegions from "@ucd-lib/theme-sass/5_layout/_l-grid-regions.css.js";
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

    select {
      background-color: #B0D0ED;
      color: #022851;
      font-weight: 700;
      border: none;
      font-size: 1rem;
    }
    select:focus {
      background-color: #b0d0ed;
      border: 1px solid #999;
    }
    .sort-container {
      display: flex;
    }
    .sort-container .section-label {
      font-weight: 700;
    }
    .sort-container label {
      margin-left: .25rem;
    }
    .separator {
      margin: 2rem 0;
      border-bottom: 4px dotted #FFBF00;
    }
    @media (max-width: 330px) {
      .sort-container {
        flex-direction: column;
      }
    }
    .project {
      margin-bottom: 2rem;
    }
    .project__meta {
      font-style: italic;
      margin-bottom: .5rem;
      font-size: .875rem;
    }
    .project__excerpt {
      margin-bottom: .5rem;
    }
    .fw-bold {
      font-weight: 700;
    }
  `;

  return [
    normalize,
    headings,
    formStyles,
    formsClasses,
    icons,
    l3col,
    lgridRegions,
    spaceUtils,
    ElementStatusController.styles,
    elementStyles
  ];
}

/**
 * @description main render function for the element
 */
export function render() {
  if ( !this.SsrPropertiesLoaded ) return html``;
  return html`
    ${ renderSearchForm.call(this) }
    <div class='separator'></div>
    ${ renderResults.call(this) }
`;}

/**
 * @description render the search results or loading/error message
 */
function renderResults(){
  if ( this.status == 'loading' ) return this.statusController.renderLoading();
  if ( this.status == 'loaded' ) return html`
    <div id='loaded'>
      <div ?hidden=${this.projects.length}>${this.statusController.renderNoResults()}</div>
      <div ?hidden=${!this.projects.length}>
        ${this.projects.map(project => html`
          <div class='project'>
            <div class='project__content'>
              <h4>${project.title}</h4>
              <div class='project__meta'>
                <div ?hidden=${!project.themes?.length}>
                  <span class='fw-bold'>Themes:</span> ${(project.themes || [])?.map(theme => theme.name).join(' | ')}
                </div>
                <div ?hidden=${!project.approaches?.length}>
                  <span class='fw-bold'>Approaches:</span> ${(project.approaches || [])?.map(approach => approach.name).join(' | ')}
                </div>
                <div ?hidden=${!project.status?.name}>
                  <span class='fw-bold'>Status:</span> ${project.status?.name || ''}${project.status?.endYear ? ` (${project.status.endYear})` : ''}
                </div>
              </div>
              <div class='project__excerpt'>${project.excerpt}</div>
              <div class='u-space-mb--small' ?hidden=${!project.partners?.length}>
                <span>Project Partners:</span> ${(project.partners || [])?.map(partner => partner.name).join(' | ')}
              </div>
              <div ?hidden=${!project.showLink}>
                <a href=${project.permalink} class="icon icon--circle-arrow-right">More about ${project.title}</a>
              </div>
            </div>
          </div>
        `)}
      </div>
      <div ?hidden=${!this.totalPages}>
        <ucd-theme-pagination
          current-page="${this.currentPage}"
          ellipses
          xs-screen
          @page-change=${(e) => this._onFieldInput('currentPage', e.detail.page)}
          max-pages=${this.totalPages}>
        </ucd-theme-pagination>
    </div>
    </div>
  `;
  return this.statusController.renderError(this.errorMessage);
}

/**
 * @description render the search/filter form
 */
function renderSearchForm(){
  return html`
    <form @submit=${this._onSubmit}>
      <div class='l-3col'>
        <div class='l-first u-space-mb'>
          ${ renderFilterSelect.call(this, this.themeFilters, 'selectedTheme', 'Any Theme', 'Filter by theme') }
        </div>
        <div class='l-second u-space-mb'>
          ${ renderFilterSelect.call(this, this.approachFilters, 'selectedApproach', 'Any Approach', 'Filter by approach') }
        </div>
        <div class='l-third u-space-mb'>
          ${ renderFilterSelect.call(this, this.statusFilters, 'selectedStatus', 'Any Status', 'Filter by status') }
        </div>
      </div>
      <div class='sort-container'>
        <div class='section-label'>Sort:</div>
        <div class='radio'>
          <input type='radio' id='sort-date' .checked=${!this.orderBy} @change=${(e) => this._onFieldInput('orderBy', '')} />
          <label for='sort-date'>Newest First</label>
        </div>
        <div class='radio'>
          <input type='radio' id='sort-title' .checked=${this.orderBy == 'title'} @change=${(e) => this._onFieldInput('orderBy', 'title')} />
          <label for='sort-title'>A - Z</label>
        </div>
      </div>
    </form>
  `
}

/**
 * @description render taxonomy filter select
 * @param {Array} options array of taxonomy options
 * @param {String} selectedProp element property to bind selected value to
 * @param {String} defaultLabel default label for select
 * @param {String} ariaLabel aria label for select
 */
function renderFilterSelect(options, selectedProp, defaultLabel, ariaLabel){
  return html`
    <select
      role="link"
      aria-label=${ariaLabel}
      @change=${(e) => this._onFieldInput(selectedProp, e.target.value)}>
      <option value="" ?selected=${!this[selectedProp]}>${defaultLabel}</option>
      ${options.map(option => html`
        <option value=${option.slug} ?selected=${this[selectedProp] == option.slug}>${option.name}</option>`
      )}
    </select>
  `;
}
