lib.stdContent >
lib.stdContent = FLUIDTEMPLATE
lib.stdContent {

	templateRootPaths < plugin.tx_contentelements.view.templateRootPaths
	partialRootPaths < plugin.tx_contentelements.view.partialRootPaths
	layoutRootPaths < plugin.tx_contentelements.view.layoutRootPaths
	templateName = Default

	settings {
		defaultHeaderType = {$content.defaultHeaderType}
		shortcutTables = {$content.shortcut.tables}
	}
}