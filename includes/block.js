
( function( blocks, editor, element ) {

	var createElement  = element.createElement
	var BlockControls = wp.blockEditor.BlockControls
	var SelectControl = wp.components.SelectControl
	var Toolbar = wp.components.Toolbar
	var quizzes = fca_qc_gutenblock_script_data.quizzes

	blocks.registerBlockType( 'quiz-cat/gutenblock', {
		title: 'Quiz Cat Quiz',
		icon: 'welcome-learn-more',
		category: 'widgets',
		keywords: ['quiz', 'quizzes', 'test' ],
		edit: function( props ) {
			return [
				createElement(
					BlockControls,
					{ 
						key: 'qc-controls'
					},		
					createElement(
						SelectControl,
						{	
							className: 'fca-qc-gutenblock-select',
							value: props.attributes.post_id,
							options: quizzes,
							onChange: function( newValue ){ props.setAttributes({ post_id: newValue }) }
						}
					),
					props.attributes.post_id == 0 ? '' : 
					createElement(
						'a',
						{	
							href: fca_qc_gutenblock_script_data.editurl + '?post=' + props.attributes.post_id + '&action=edit',
							target: '_blank',
							className: 'fca-qc-gutenblock-link'
						},
						'Edit'
					),
					createElement(
						'a',
						{	
							href: fca_qc_gutenblock_script_data.newurl + '?post_type=fca_qc_quiz',
							target: '_blank',
							className: 'fca-qc-gutenblock-link'
						},
						'New'
					)
				),
				createElement( wp.serverSideRender, {
					key: 'qc_ssr',
					block: 'quiz-cat/gutenblock',
					attributes:  props.attributes,
				})
			]
		},

		save: function( props ) {
			return null
		},
	} )
}(
	window.wp.blocks,
	window.wp.editor,
	window.wp.element
))