/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { createPortal, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { EidWrapper } from '../common/components/eid-wrapper';
import { getLiveSiteControlProperty } from '../common/utils/gdlLiveSiteControlDataHelpers';

export default function LiveSitePublishGuideButton( { launchWorkflow } ) {
	const [ publishGuideTriggerNode, setPublishGuideTriggerNode ] = useState( null );

	const [ sitePublish ] = useEntityProp( 'root', 'site', getLiveSiteControlProperty( 'settings' )?.publishState );

	const {
		isPublishGuideFabActive,
	} = useSelect( ( select ) => ( {
		isPublishGuideFabActive: select( 'godaddy-launch/publish-guide' ).isPublishGuideFabActive(),
	} ), [] );

	const classSelector = '.publish-guide-popover__footer';

	useEffect( () => {
		let newPublishGuideTrigger = document.querySelector( classSelector );

		setTimeout( () => {
			newPublishGuideTrigger = document.querySelector( classSelector );
			setPublishGuideTriggerNode( newPublishGuideTrigger );
		} );
	}, [ isPublishGuideFabActive ] );

	if ( ! publishGuideTriggerNode ) {
		return null;
	}

	return ( ! sitePublish ) && createPortal(
		<div className="wrap gdl-live-site-button__container">
			<EidWrapper
				action="click"
				section="guide"
				target="launch"
			>
				<Button
					isPrimary
					onClick={ launchWorkflow }
				>
					{ __( 'Launch My Site', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
		</div>,
		publishGuideTriggerNode
	);
}
