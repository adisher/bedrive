import {Accordion, AccordionItem} from '@common/ui/accordion/accordion';
import {Trans} from '@common/i18n/trans';
import {appearanceState} from '@common/admin/appearance/appearance-store';
import {useFieldArray} from 'react-hook-form';
import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {LandingPageContent} from '@app/landing/landing-page-content';
import {Button} from '@common/ui/buttons/button';
import {AddIcon} from '@common/icons/material/Add';
import {useState} from 'react';
import {FormImageSelector} from '@common/ui/images/image-selector';

export function LandingPageSecondaryFeatures() {
  const {fields, remove, append} = useFieldArray({
    name: 'settings.homepage.appearance.secondaryFeatures',
  });
  const [expandedValues, setExpandedValues] = useState([0]);
  return (
    <div>
      <Accordion
        variant="outline"
        expandedValues={expandedValues}
        onExpandedChange={values => {
          setExpandedValues(values as number[]);
          if (values.length) {
            appearanceState().preview.setHighlight(
              `[data-testid="secondary-root-${values[0]}"]`
            );
          }
        }}
      >
        {fields.map((field, index) => {
          return (
            <AccordionItem
              key={field.id}
              value={index}
              label={<Trans message={`Secondary feature ${index + 1}`} />}
            >
              <FeatureForm index={index} />
              <div className="text-right">
                <Button
                  size="xs"
                  variant="outline"
                  color="danger"
                  onClick={() => {
                    remove(index);
                  }}
                >
                  <Trans message="Remove" />
                </Button>
              </div>
            </AccordionItem>
          );
        })}
      </Accordion>
      <div className="mt-20 text-right">
        <Button
          size="xs"
          variant="outline"
          color="primary"
          startIcon={<AddIcon />}
          onClick={() => {
            append({});
            setExpandedValues([fields.length]);
          }}
        >
          <Trans message="Add feature" />
        </Button>
      </div>
    </div>
  );
}

interface FeatureFormProps {
  index: number;
}
function FeatureForm({index}: FeatureFormProps) {
  return (
    <>
      <FormImageSelector
        name={`settings.homepage.appearance.secondaryFeatures.${index}.image`}
        className="mb-30"
        label={<Trans message="Image" />}
        defaultValue={getDefaultImage(index)}
        diskPrefix="homepage"
      />
      <FormTextField
        name={`settings.homepage.appearance.secondaryFeatures.${index}.title`}
        label={<Trans message="Title" />}
        className="mb-20"
        onFocus={() => {
          appearanceState().preview.setHighlight(
            `[data-testid="secondary-title-${index}"]`
          );
        }}
      />
      <FormTextField
        name={`settings.homepage.appearance.secondaryFeatures.${index}.subtitle`}
        label={<Trans message="Subtitle" />}
        className="mb-20"
        inputElementType="textarea"
        rows={4}
        onFocus={() => {
          appearanceState().preview.setHighlight(
            `[data-testid="secondary-subtitle-${index}"]`
          );
        }}
      />
      <FormTextField
        name={`settings.homepage.appearance.secondaryFeatures.${index}.description`}
        label={<Trans message="Description" />}
        className="mb-20"
        inputElementType="textarea"
        rows={4}
        onFocus={() => {
          appearanceState().preview.setHighlight(
            `[data-testid="secondary-description-${index}"]`
          );
        }}
      />
    </>
  );
}

function getDefaultImage(index: number): string | undefined {
  return (
    appearanceState().defaults?.settings.homepage
      .appearance as LandingPageContent
  ).secondaryFeatures[index]?.image;
}
