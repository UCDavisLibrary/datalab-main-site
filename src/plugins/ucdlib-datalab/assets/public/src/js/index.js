import "@ucd-lib/brand-theme";

class DynamicScriptLoader {

  constructor() {
    this.loaded = {};
    this.registration = [
      {
        name: 'jobs-board-admin',
        cssQuery: ['ucdlib-dl-jobs-board-admin']
      },
      {
        name: 'jobs-board',
        cssQuery: ['ucdlib-dl-jobs-board']
      },
      {
        name: 'projects',
        cssQuery: ['ucdlib-dl-projects']
      }
    ];
  }


  async load() {
    for( let bundle of this.registration ) {
      if( bundle.cssQuery ) {
        if ( !Array.isArray(bundle.cssQuery) ){
          bundle.cssQuery = [bundle.cssQuery];
        }
        for (const q of bundle.cssQuery) {
          if ( document.querySelector(q) ){
            this.loadWidgetBundle(bundle.name);
          }
        }
      }
    }
  }

  loadWidgetBundle(bundleName) {
    if( typeof bundleName !== 'string' ) return;
    if( this.loaded[bundleName] ) return this.loaded[bundleName];

    if ( bundleName == 'jobs-board-admin' ){
      this.loaded[bundleName] = import(/* webpackChunkName: "jobs-board-admin" */ './elements/jobs-board/ucdlib-dl-jobs-board-admin.js');
    } else if ( bundleName == 'jobs-board' ){
      this.loaded[bundleName] = import(/* webpackChunkName: "jobs-board" */ './elements/jobs-board/ucdlib-dl-jobs-board.js');
    } else if ( bundleName == 'projects' ){
      this.loaded[bundleName] = import(/* webpackChunkName: "projects" */ './elements/projects/ucdlib-dl-projects.js');
    }
    return this.loaded[bundleName]
  }

}

let loaderInstance = new DynamicScriptLoader();
if( document.readyState === 'complete' ) {
  loaderInstance.load();
} else {
  window.addEventListener('load', () => {
    loaderInstance.load();
  });
}
