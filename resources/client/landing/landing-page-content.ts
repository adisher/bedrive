import {MenuItemConfig} from '@common/core/settings/settings';

export interface LandingPageContent {
  headerTitle: string;
  headerSubtitle: string;
  headerImage: string;
  headerImageOpacity: string;
  headerOverlayColor1: string;
  headerOverlayColor2: string;
  footerTitle: string;
  footerSubtitle: string;
  footerImage: string;
  actions: {
    cta1: MenuItemConfig;
    cta2: MenuItemConfig;
    cta3: MenuItemConfig;
  };
  primaryFeatures: {
    title: string;
    subtitle: string;
    image: string;
  }[];
  secondaryFeatures: {
    title: string;
    subtitle: string;
    description: string;
    image: string;
  }[];
}
