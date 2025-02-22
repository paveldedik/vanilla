/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { StoryHeading } from "@library/storybook/StoryHeading";
import { storiesOf } from "@storybook/react";
import React from "react";
import { StoryTiles } from "@library/storybook/StoryTiles";
import Button from "@library/forms/Button";
import { StoryContent } from "@library/storybook/StoryContent";
import { StoryTileAndTextCompact } from "@library/storybook/StoryTileAndTextCompact";
import { buttonClasses, ButtonTypes, buttonUtilityClasses } from "@library/forms/buttonStyles";
import { StoryParagraph } from "@library/storybook/StoryParagraph";
import { StoryTile } from "@library/storybook/StoryTile";
import { globalVariables } from "@library/styles/globalStyleVars";
import { unit } from "@library/styles/styleHelpers";
import { CheckCompactIcon, CloseCompactIcon } from "@library/icons/common";
import { ComposeIcon } from "@library/icons/titleBar";
import Paragraph from "@library/layout/Paragraph";
const reactionsStory = storiesOf("Components", module);

reactionsStory.add("Buttons", () => {
    const globalVars = globalVariables();
    const classesButtonUtility = buttonUtilityClasses();
    return (
        <StoryContent>
            <StoryHeading depth={1}>Buttons</StoryHeading>
            <StoryParagraph>
                Buttons use a{" "}
                <strong>
                    <code>baseClass</code>
                </strong>{" "}
                to specify the type of button you want. The types are available through the enum{" "}
                <strong>
                    <code>ButtonTypes</code>
                </strong>{" "}
                and if you want to do something custom and not overwrite the base button styles, use
                <strong>
                    {" "}
                    <code>ButtonTypes.CUSTOM</code>
                </strong>
                .
            </StoryParagraph>
            <StoryTiles>
                <StoryTileAndTextCompact title={"Most common button"}>
                    <Button>Standard</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact title={"Call to action"}>
                    <Button baseClass={ButtonTypes.PRIMARY}>Primary</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact type="titleBar" title={"For Title Bar (Sign in Button)"}>
                    <Button baseClass={ButtonTypes.TRANSPARENT}>Transparent</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact type="titleBar" text={"For Title Bar (Register)"}>
                    <Button baseClass={ButtonTypes.TRANSLUCID}>Translucid</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact>
                    <Button baseClass={ButtonTypes.TEXT}>Text</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact>
                    <Button baseClass={ButtonTypes.TEXT_PRIMARY}>Text Primary</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact
                    title={`Icon (${unit(globalVars.buttonIcon.size)} x ${unit(globalVars.buttonIcon.size)})`}
                >
                    <Button baseClass={ButtonTypes.ICON} title={"Icon"}>
                        <CloseCompactIcon />
                    </Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact
                    title={`Icon Compact (${unit(globalVars.icon.sizes.default)}px x ${unit(
                        globalVars.icon.sizes.default,
                    )})`}
                >
                    <Button baseClass={ButtonTypes.ICON_COMPACT}>
                        <CheckCompactIcon />
                    </Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact
                    text={
                        "If you don't want to fight against existing styles and write your own custom button, use the custom class."
                    }
                >
                    <Button baseClass={ButtonTypes.CUSTOM}>Custom</Button>
                </StoryTileAndTextCompact>
            </StoryTiles>

            <StoryHeading>Button With Icon</StoryHeading>
            <StoryParagraph>
                {"You can just add an icon in with the text of your button. It's worth noting however, that there might be\n" +
                    'a "compact" version of your icon that doesn\'t have as much padding that will look better.'}
            </StoryParagraph>
            <StoryTiles>
                <StoryTileAndTextCompact>
                    <Button baseClass={ButtonTypes.STANDARD}>
                        <ComposeIcon className={classesButtonUtility.buttonIconRightMargin} />
                        {"Icon and Text"}
                    </Button>
                </StoryTileAndTextCompact>
            </StoryTiles>
        </StoryContent>
    );
});

reactionsStory.add("Modals", () => {
    return (
        <StoryContent>
            <StoryHeading depth={1}>Modals</StoryHeading>
            <StoryParagraph>
                Click button to see modals. Note that they are rendered through a{" "}
                <a href="https://reactjs.org/docs/portals.html" rel="noopener noreferrer" target="_blank">
                    react portal.
                </a>
            </StoryParagraph>

            <StoryTiles>
                <StoryTileAndTextCompact>
                    <Button>Standard Modal</Button>
                </StoryTileAndTextCompact>
                <StoryTileAndTextCompact>
                    <Button>Standard Modal</Button>
                </StoryTileAndTextCompact>
            </StoryTiles>
        </StoryContent>
    );
});

reactionsStory.add("Examples", () => {
    return <StoryHeading depth={1}>Modal Examples</StoryHeading>;
});
