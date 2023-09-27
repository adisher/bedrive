import {MenuItemForm} from '@common/admin/menus/menu-item-form';
import {Accordion, AccordionItem} from '@common/ui/accordion/accordion';
import {Trans} from '@common/i18n/trans';
import {appearanceState} from '@common/admin/appearance/appearance-store';
import {useState} from 'react';

export function LandingPageSectionActionButtons() {
  const [expandedValues, setExpandedValues] = useState(['cta1']);
  return (
    <Accordion
      variant="outline"
      expandedValues={expandedValues}
      onExpandedChange={values => {
        setExpandedValues(values as string[]);
        if (values.length) {
          appearanceState().preview.setHighlight(
            `[data-testid="${values[0]}"]`
          );
        }
      }}
    >
      <AccordionItem value="cta1" label={<Trans message="Header button 1" />}>
        <MenuItemForm formPathPrefix="settings.homepage.appearance.actions.cta1" />
      </AccordionItem>
      <AccordionItem value="ct2" label={<Trans message="Header button 2" />}>
        <MenuItemForm formPathPrefix="settings.homepage.appearance.actions.cta2" />
      </AccordionItem>
      <AccordionItem value="cta3" label={<Trans message="Footer button" />}>
        <MenuItemForm formPathPrefix="settings.homepage.appearance.actions.cta3" />
      </AccordionItem>
    </Accordion>
  );
}
