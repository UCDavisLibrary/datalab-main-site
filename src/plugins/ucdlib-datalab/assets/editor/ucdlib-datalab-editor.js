import "@ucd-lib/brand-theme-editor";
import { registerBlockType } from '@wordpress/blocks';
import customBlocks from "./lib/blocks";

customBlocks.forEach(block => {
  registerBlockType( block.name, block.settings );
});
