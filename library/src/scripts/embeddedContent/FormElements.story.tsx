/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { StoryHeading } from "@library/storybook/StoryHeading";
import { storiesOf } from "@storybook/react";
import React from "react";
import { StoryContent } from "@library/storybook/StoryContent";
import { StoryParagraph } from "@library/storybook/StoryParagraph";
import RadioTabs from "@library/forms/radioTabs/RadioTabs";
import { t } from "@library/utility/appUtils";
import RadioTab from "@library/forms/radioTabs/RadioTab";
import InputBlock from "@library/forms/InputBlock";
import InputTextBlock from "@library/forms/InputTextBlock";
import MultiUserInput from "@library/features/users/MultiUserInput";
import { IComboBoxOption } from "@library/features/search/SearchBar";
import DateRange from "@library/forms/DateRange";
import Checkbox from "@library/forms/Checkbox";
import StoryExampleDropDownSearch from "@library/embeddedContent/StoryExampleDropDownSearch";
import { uniqueIDFromPrefix } from "@library/utility/idUtils";
import RadioButton from "@library/forms/RadioButton";
import { inputBlockClasses } from "@library/forms/InputBlockStyles";
import "@library/forms/datePicker.scss";

const reactionsStory = storiesOf("FormElements", module);

// Radio as tabs

const doNothing = () => {};

reactionsStory.add("Form Elements", () => {
    let activeTab = "Tab A";
    const classesInputBlock = inputBlockClasses();

    const doNothing = () => {
        return;
    };

    /**
     * Simple form setter.
     */
    const handleUserChange = (options: IComboBoxOption[]) => {
        // Do something
        doNothing();
    };

    // To avoid clashing with other components also using these radio buttons, you need to generate a unique ID for the group.

    const radioButtonGroup1 = uniqueIDFromPrefix("radioButtonGroupA");

    return (
        <StoryContent>
            <StoryHeading depth={1}>Form Elements</StoryHeading>
            <StoryHeading>Checkbox</StoryHeading>
            <Checkbox label={t("Simple Checkbox")} />
            <StoryHeading>Radio Buttons - In Group</StoryHeading>

            <InputBlock label={"Gaggle of radio buttons"}>
                <RadioButton label={"Option A"} name={radioButtonGroup1} />
                <RadioButton label={"Option B"} name={radioButtonGroup1} />
                <RadioButton label={"Option C"} name={radioButtonGroup1} />
                <RadioButton label={"Option D"} name={radioButtonGroup1} />
                <RadioButton label={"Option E"} name={radioButtonGroup1} />
            </InputBlock>

            <StoryHeading>InputBlock</StoryHeading>
            <StoryParagraph>Helper component to add label to various inputs</StoryParagraph>
            <InputBlock label={"Example of label that can be used for any input"}>
                <div>{"[Some Input]"}</div>
            </InputBlock>
            <StoryHeading>Input Text Block</StoryHeading>
            <InputTextBlock label={t("Text Input")} inputProps={{}} />
            <StoryHeading>Input Text (password type)</StoryHeading>
            <StoryParagraph>You can set the `type` of the text input to any standard HTML5 values.</StoryParagraph>
            <InputTextBlock label={t("Password")} type={"password"} inputProps={{ type: "password" }} />
            <StoryHeading>Radio Buttons styled as tabs</StoryHeading>
            <StoryParagraph>
                The state for this component needs to be managed by the parent. (Will not update here when you click)
            </StoryParagraph>
            <RadioTabs
                accessibleTitle={t("Search in:")}
                prefix="advancedSearchDomain"
                setData={doNothing}
                activeTab={activeTab}
                childClass="advancedSearchDomain-tab"
            >
                <RadioTab label={t("Tab A")} position="left" data={"Tab A"} />
                <RadioTab label={t("Tab B")} position="right" data={"Tab B"} />
            </RadioTabs>

            <StoryHeading>Tokens Input</StoryHeading>
            <MultiUserInput
                onChange={handleUserChange}
                value={[
                    {
                        value: "Astérix",
                        label: "Astérix",
                    },
                    {
                        value: "Obélix",
                        label: "Obélix",
                    },
                    {
                        value: "Idéfix",
                        label: "Idéfix",
                    },
                    {
                        value: "Panoramix",
                        label: "Panoramix",
                    },
                ]}
            />

            <StoryHeading>DropDown with search</StoryHeading>
            <StoryExampleDropDownSearch onChange={doNothing} />

            <StoryHeading>Date Range</StoryHeading>
            <DateRange onStartChange={doNothing} onEndChange={doNothing} start={undefined} end={undefined} />
        </StoryContent>
    );
});
