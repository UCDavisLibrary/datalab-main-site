import "@ucd-lib/brand-theme-editor";
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';
import customBlocks from "./lib/blocks";
import { select } from "@wordpress/data";

import projectSettings from "./lib/plugins/project-settings";
import hackathonSettings from "./lib/plugins/hackathon-settings";

customBlocks.forEach(block => {
  registerBlockType( block.name, block.settings );
});

if ( select('core/editor') ){
  registerPlugin( projectSettings.name, projectSettings.settings );
  registerPlugin( hackathonSettings.name, hackathonSettings.settings );
};

