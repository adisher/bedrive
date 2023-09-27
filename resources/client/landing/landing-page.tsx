import clsx from 'clsx';
import {LandingPageContent} from './landing-page-content';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Button, ButtonProps} from '@common/ui/buttons/button';
import {IconButton} from '@common/ui/buttons/icon-button';
import {KeyboardArrowDownIcon} from '@common/icons/material/KeyboardArrowDown';
import {MixedImage} from '@common/ui/images/mixed-image';
import {Footer} from '@common/ui/footer/footer';
import {Trans} from '@common/i18n/trans';
import {useIsMobileMediaQuery} from '@common/utils/hooks/is-mobile-media-query';
import {AdHost} from '@common/admin/ads/ad-host';
import {Link} from 'react-router-dom';
import {createSvgIconFromTree} from '@common/icons/create-svg-icon';
import {MenuItemConfig} from '@common/core/settings/settings';
import {Fragment} from 'react';
import {DefaultMetaTags} from '@common/seo/default-meta-tags';
import {useTrans} from '@common/i18n/use-trans';
import {useSettings} from '@common/core/settings/use-settings';

interface ContentProps {
  content: LandingPageContent;
}
export function LandingPage() {
  const settings = useSettings();
  const homepage = settings.homepage as {appearance: LandingPageContent};

  return (
    <Fragment>
      <DefaultMetaTags />
      <div className="h-full overflow-y-auto scroll-smooth">
        <HeroHeader content={homepage.appearance} />
        <AdHost slot="landing-top" className="mb-14 md:mb-60 mx-14" />
        <PrimaryFeatures content={homepage.appearance} />
        <div className="h-1 bg-divider my-40" />
        <SecondaryFeatures content={homepage.appearance} />
        <BottomCta content={homepage.appearance} />
        <Footer className="landing-container" />
      </div>
    </Fragment>
  );
}

function HeroHeader({
  content: {
    headerTitle,
    headerSubtitle,
    headerImage,
    headerImageOpacity,
    actions,
    headerOverlayColor1,
    headerOverlayColor2,
  },
}: ContentProps) {
  const isMobile = useIsMobileMediaQuery();
  const {trans} = useTrans();

  let overlayBackground = undefined;

  if (headerOverlayColor1 && headerOverlayColor2) {
    overlayBackground = `linear-gradient(45deg, ${headerOverlayColor1} 0%, ${headerOverlayColor2} 100%)`;
  } else if (headerOverlayColor1) {
    overlayBackground = headerOverlayColor1;
  } else if (headerOverlayColor2) {
    overlayBackground = headerOverlayColor2;
  }

  return (
    <header
      className="relative h-500 md:h-full mb-14 md:mb-60"
      style={{background: overlayBackground}}
    >
      <div
        data-testid="headerImage"
        className="absolute inset-0 z-10 bg-cover bg-no-repeat bg-1/2 bg-fixed"
        style={{
          backgroundImage: `url(${headerImage})`,
          opacity: headerImageOpacity,
        }}
      />
      <div className="flex flex-col relative h-full z-20">
        <Navbar
          color="transparent"
          className="flex-shrink-0"
          menuPosition="homepage-navbar"
        />
        <div className="flex-auto flex flex-col items-center justify-center text-white max-w-850 mx-auto text-center px-14">
          {headerTitle && (
            <h1
              className="text-4xl md:text-5xl font-medium"
              data-testid="headerTitle"
            >
              <Trans message={headerTitle} />
            </h1>
          )}
          {headerSubtitle && (
            <div
              className="text-lg md:text-xl mt-30 md:mt-10"
              data-testid="headerSubtitle"
            >
              <Trans message={headerSubtitle} />
            </div>
          )}
          <div className="flex gap-20 mt-30 min-h-50">
            <CtaButton
              item={actions?.cta1}
              variant="raised"
              color="primary"
              size="lg"
              radius="rounded-full"
              data-testid="cta1"
              className="min-w-180"
            />
            <CtaButton
              item={actions?.cta2}
              variant="text"
              color="paper"
              size="lg"
              radius="rounded-full"
              data-testid="cta2"
            />
          </div>
        </div>
      </div>
      {!isMobile && (
        <IconButton
          size="lg"
          className="absolute bottom-5 z-30 text-white mx-auto left-0 right-0"
          elementType="a"
          aria-label={trans({message: 'View features'})}
          href="#primary-features"
        >
          <KeyboardArrowDownIcon />
        </IconButton>
      )}
    </header>
  );
}

interface CtaButtonProps extends ButtonProps {
  item?: MenuItemConfig;
}
function CtaButton({item, ...buttonProps}: CtaButtonProps) {
  if (!item?.label) return null;
  const Icon = item.icon ? createSvgIconFromTree(item.icon) : undefined;
  return (
    <Button
      elementType={item.type === 'route' ? Link : 'a'}
      href={item.action}
      to={item.action}
      startIcon={Icon ? <Icon /> : undefined}
      {...buttonProps}
    >
      <Trans message={item.label} />
    </Button>
  );
}

function PrimaryFeatures({content}: ContentProps) {
  return (
    <div
      className="md:flex items-stretch gap-26 landing-container"
      id="primary-features"
    >
      {content?.primaryFeatures?.map((feature, index) => (
        <div
          key={index}
          className="flex-1 px-24 py-36 rounded-2xl mb-14 md:mb-0 shadow-[0_10px_30px_rgba(0,0,0,0.08)] text-center"
          data-testid={`primary-root-${index}`}
        >
          <MixedImage
            className="h-128 mx-auto mb-30"
            data-testid={`primary-image-${index}`}
            src={feature.image}
          />
          <h2
            className="my-16 text-lg font-medium"
            data-testid={`primary-title-${index}`}
          >
            <Trans message={feature.title} />
          </h2>
          <div
            className="text-md text-[0.938rem]"
            data-testid={`primary-subtitle-${index}`}
          >
            <Trans message={feature.subtitle} />
          </div>
        </div>
      ))}
    </div>
  );
}

function SecondaryFeatures({content}: ContentProps) {
  return (
    <div className="landing-container">
      {content?.secondaryFeatures?.map((feature, index) => {
        const isEven = index % 2 === 0;
        return (
          <div
            key={index}
            data-testid={`secondary-root-${index}`}
            className={clsx(
              'md:flex py-16 mb-14 md:mb-80',
              isEven && 'flex-row-reverse'
            )}
          >
            <img
              src={feature.image}
              className="rounded-lg max-w-full mr-auto shadow-[0_10px_30px_rgba(0,0,0,0.08)] w-580"
              data-testid={`secondary-image-${index}`}
              alt=""
            />
            <div className="ml-30 mr-auto max-w-350 pt-30">
              <small
                className="uppercase mb-16 tracking-widest font-medium text-xs text-muted"
                data-testid={`secondary-subtitle-${index}`}
              >
                <Trans message={feature.subtitle} />
              </small>
              <h3
                className="py-16 text-3xl"
                data-testid={`secondary-title-${index}`}
              >
                <Trans message={feature.title} />
              </h3>
              <div className="w-50 h-2 bg-black/90" />
              <div
                className="my-20 text-[0.938rem]"
                data-testid={`secondary-description-${index}`}
              >
                <Trans message={feature.description} />
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}

function BottomCta({content}: ContentProps) {
  return (
    <div
      className="relative py-70 text-on-primary bg-no-repeat bg-fixed text-center bg-[#2B2B2B]"
      style={{backgroundImage: `url("${content.footerImage}")`}}
      data-testid="footerImage"
    >
      <h2
        className="text-3xl mx-auto max-w-620 font-normal"
        data-testid="footerTitle"
      >
        <Trans message={content.footerTitle} />
      </h2>
      {content.footerSubtitle && (
        <p
          className="text-2xl mx-auto max-w-620 font-normal mt-50"
          data-testid="footerSubtitle"
        >
          <Trans message={content.footerSubtitle} />
        </p>
      )}
      <CtaButton
        item={content.actions?.cta3}
        size="lg"
        variant="outline"
        color="paper"
        className="block mt-50"
        data-testid="cta3"
      />
    </div>
  );
}
