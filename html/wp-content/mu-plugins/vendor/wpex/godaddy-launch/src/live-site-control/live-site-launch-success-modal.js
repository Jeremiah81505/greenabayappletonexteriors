/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { GlobeIcon } from '@godaddy-wordpress/coblocks-icons';
import { useCopyToClipboard } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import { Icon, pages } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { ReactComponent as AiroCTAIllustration } from './airo-cta-illustration.svg';
import { ReactComponent as AiroLogo } from './airo-logo.svg';
import Confetti from '../common/components/confetti';
import { getLiveSiteControlProperty } from '../common/utils/gdlLiveSiteControlDataHelpers';
import { ReactComponent as GoDaddyLogo } from './godaddy-logo.svg';
import { ReactComponent as LiveSiteModalDivider } from './live-site-modal-divider.svg';
import { logInteractionEvent } from '../common/utils/instrumentation';
import { EID_PREFIX, EidWrapper } from '../common/components/eid-wrapper';

const SitePreview = ( { url } ) => {
	return (
		<div className="gdl-live-site-preview">
			<div className="gdl-live-site-preview__container">
				<iframe src={ url + '?gdl-live-control-preview=true' } title="site-preview" />
			</div>
		</div>
	);
};

const LiveSiteLaunchSuccessModal = ( { closeModal } ) => {
	const [ copyText, setCopyText ] = useState( __( 'Copy the URL', 'godaddy-launch' ) );

	const forceHttps = ( url ) => {
		if ( url.includes( '//localhost' ) || url.includes( '//gdl.test' ) ) {
			return url;
		}

		return url.replace( 'http:/', 'https:/' );
	};

	const { url } = useSelect( ( select ) => {
		return {
			url: forceHttps( select( 'core' ).getSite()?.url || window.location.origin ),
		};
	} );

	const ref = useCopyToClipboard( url, () => setCopyText( __( 'Copied!', 'godaddy-launch' ) ) );

	return (
		<>
			<Modal
				className="gdl-launch-site-success-modal godaddy-styles"
				onRequestClose={ closeModal }
				title={ __( 'Good work! Your site is live.', 'godaddy-launch' ) }
			>
				<div className="gdl-post-publish-social-media-modal__site-preview">
					<SitePreview url={ url } />
				</div>
				<div className="gdl-launch-site-success-modal__content">
					<div className="gdl-launch-site-success-modal__site-description-container">
						<div className="gdl-launch-site-success-modal__site-description-container__icon-container border-right">
							<Icon icon={ GlobeIcon } size={ 18 } />
						</div>
						<p className="gdl-launch-site-success-modal__site-description-container__site-name">{ url }</p>
					</div>
					<EidWrapper
						action="click"
						section="gdl_launch_site_success_modal/copy_url"
						target="button"
					>
						<Button
							icon={ <Icon icon={ pages } /> }
							isSecondary
							ref={ ref }
						>
							{ copyText }
						</Button>
					</EidWrapper>
				</div>
				<div className="gdl-launch-site-success-modal__cta-container">
					<Button
						href={ url }
						isPrimary
						target="_blank"
					>
						{ __( 'View Site', 'godaddy-launch' ) }
					</Button>
				</div>
				<div className="gdl-launch-site-success-modal__divider">
					<LiveSiteModalDivider />
				</div>
				<div className="gdl-launch-site-success-modal__airo-cta">
					<div className="airo-cta-text-container">
						<div className="gd-airo-logo-container">
							<GoDaddyLogo />
							<AiroLogo />
						</div>
						<div className="gd-airo-cta__title-container">
							<p className="gd-airo-cta__title">Improve your whole site with GoDaddy Airo Site Optimizer</p>
							<p className="gd-airo-cta__subtitle">Get recommendations to improve your site and get instant updates with this AI-powered tool.</p>
							<Button
								className="gd-airo-cta__button"
								href={ `https://host.godaddy.com/mwp/site/${ getLiveSiteControlProperty( 'siteId' ) }/site-optimizer ` }
								onClick={ () => logInteractionEvent( { eid: `${ EID_PREFIX }.site-optimizer-navigation.click` } ) }
								target="_blank"
								variant="secondary"
							>
								Get Started
							</Button>
						</div>
					</div>
					<div className="gd-airo-cta-illustration-container">
						<AiroCTAIllustration />
					</div>
				</div>
			</Modal>
			{ Confetti( true, true ) }
		</>
	);
};

export default LiveSiteLaunchSuccessModal;
