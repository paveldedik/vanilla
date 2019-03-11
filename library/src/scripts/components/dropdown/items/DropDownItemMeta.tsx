/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import { dropDownClasses } from "@library/styles/dropDownStyles";
import { metasClasses } from "@library/styles/metasStyles";

interface IProps {
    className?: string;
    children: React.ReactNode;
}

/**
 * Implements meta type of item for DropDownMenu
 */
export default class DropDownItemMeta extends React.Component<IProps> {
    public render() {
        const classes = dropDownClasses();
        const classesMetas = metasClasses();
        return (
            <div className={classNames("dropDown-meta", classesMetas.metaStyle, classes.meta, this.props.className)}>
                {this.props.children}
            </div>
        );
    }
}
