/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { t } from "@library/utility/appUtils";
import classNames from "classnames";
import { iconClasses } from "@library/icons/iconClasses";

export function CloseIcon(props: { className?: string; noPadding?: boolean }) {
    const title = t("Close");
    const viewBox = props.noPadding ? "0 0 16 16" : "0 0 24 24";
    const transform = props.noPadding ? "translate(-4 -4)" : "";
    return (
        <svg
            className={classNames("icon", "icon-close", props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox={viewBox}
            aria-hidden="true"
        >
            <title>{title}</title>
            <path
                transform={transform}
                fill="currentColor"
                d="M12,10.6293581 L5.49002397,4.11938207 C5.30046135,3.92981944 4.95620859,3.96673045 4.69799105,4.22494799 L4.22494799,4.69799105 C3.97708292,4.94585613 3.92537154,5.29601344 4.11938207,5.49002397 L10.6293581,12 L4.11938207,18.509976 C3.92981944,18.6995387 3.96673045,19.0437914 4.22494799,19.3020089 L4.69799105,19.775052 C4.94585613,20.0229171 5.29601344,20.0746285 5.49002397,19.8806179 L12,13.3706419 L18.509976,19.8806179 C18.6995387,20.0701806 19.0437914,20.0332695 19.3020089,19.775052 L19.775052,19.3020089 C20.0229171,19.0541439 20.0746285,18.7039866 19.8806179,18.509976 L13.3706419,12 L19.8806179,5.49002397 C20.0701806,5.30046135 20.0332695,4.95620859 19.775052,4.69799105 L19.3020089,4.22494799 C19.0541439,3.97708292 18.7039866,3.92537154 18.509976,4.11938207 L12,10.6293581 Z"
            />
        </svg>
    );
}

export function CheckIcon(props: { className?: string }) {
    const title = `✓`;
    return (
        <svg
            className={classNames("icon", "icon-check", props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <title>{title}</title>
            <polygon fill="currentColor" points="5,12.7 3.6,14.1 9,19.5 20.4,7.9 19,6.5 9,16.8" />
        </svg>
    );
}

export function HelpIcon(props: { className?: string }) {
    const title = t("Help");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            className={classNames("icon", "icon-help", props.className)}
            role="img"
            aria-label={title}
        >
            <title>{title}</title>
            <path
                d="M12,19a7,7,0,1,0-7-7A7,7,0,0,0,12,19Zm0,1a8,8,0,1,1,8-8A8,8,0,0,1,12,20Zm-.866-6.5v-.338a2,2,0,0,1,.211-.969,2.757,2.757,0,0,1,.741-.8,4.09,4.09,0,0,0,.812-.773,1.156,1.156,0,0,0,.183-.656.826.826,0,0,0-.3-.683,1.333,1.333,0,0,0-.851-.238A2.941,2.941,0,0,0,11,9.185a6.65,6.65,0,0,0-.836.344L9.721,8.6a4.653,4.653,0,0,1,2.3-.6,2.485,2.485,0,0,1,1.645.508,1.727,1.727,0,0,1,.609,1.4,1.983,1.983,0,0,1-.117.706,2.006,2.006,0,0,1-.352.59,5.653,5.653,0,0,1-.812.731,3.088,3.088,0,0,0-.659.64,1.229,1.229,0,0,0-.166.682V13.5Zm-.217,1.688a.7.7,0,0,1,.778-.8.775.775,0,0,1,.582.209.818.818,0,0,1,.2.59.838.838,0,0,1-.2.595.878.878,0,0,1-1.156.006A.844.844,0,0,1,10.917,15.185Z"
                transform="translate(-4 -4)"
                fill="currentColor"
            />
        </svg>
    );
}

export function ComposeIcon(props: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            className={classNames("icon", "icon-compose", props.className)}
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M23.591,1.27l-.9-.9a1.289,1.289,0,0,0-1.807,0l-.762.863,2.6,2.587.868-.751a1.24,1.24,0,0,0,.248-.373,1.255,1.255,0,0,0,0-1.052A1.232,1.232,0,0,0,23.591,1.27ZM19.5,20.5H3.5V4.5H15.4l1.4-1.431H2.751A1,1,0,0,0,2,4.07V20.939a1,1,0,0,0,1,1H20.011a1,1,0,0,0,1-1V7L19.5,8.445ZM21.364,3.449l-9.875,9.8-.867-.861,9.874-9.8-.867-.863-4.938,4.9-4.938,4.9L8.74,15.167l3.617-1.055,9.875-9.8Z"
            />
        </svg>
    );
}

export function DownloadIcon(props: { className?: string }) {
    const title = t("Download");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            className={classNames("icon", "icon-compose", props.className)}
            role="img"
            aria-label={title}
        >
            <title>{title}</title>
            <path
                d="M6.483,10.462h.005a.5.5,0,0,1,.707.01l3.954,4.066V3.887a.5.5,0,0,1,.5-.5h.621a.5.5,0,0,1,.5.5V14.533l4.035-4.073h0a.5.5,0,0,1,.707,0l.437.437a.5.5,0,0,1,0,.707h0l-5.6,5.6a.5.5,0,0,1-.707,0h0l-5.6-5.6a.5.5,0,0,1,0-.707h0ZM20.25,19.5V17.25a.75.75,0,0,1,1.5,0v3A.75.75,0,0,1,21,21H3a.75.75,0,0,1-.75-.75v-3a.75.75,0,0,1,1.5,0V19.5Z"
                fill="currentColor"
            />
        </svg>
    );
}

export function VanillaLogo(props: { className?: string; fill?: string }) {
    const title = `Vanilla`;
    const classes = iconClasses();
    const fill = props.fill ? props.fill : "currentColor";
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            className={classNames(props.className, classes.vanillaLogo)}
            viewBox="0 0 296.866 119.883"
            role="img"
            aria-label={title}
        >
            <title>{title}</title>
            <path
                d="M107.031,93.8a11.987,11.987,0,0,1-4.588-3.006c-1.349-1.609-2.163-4.3-2.687-6.309-1.277-4.909-.906-10.05-.531-15.051.186-2.492.421-4.98.67-7.467.2-2.026.42-4.049.607-6.077a17.515,17.515,0,0,0,.181-3.355.63.63,0,0,0-.014-.1c-.576-3.325-5.01-4.707-8.435-2.27a8.433,8.433,0,0,0-3.044,5.032c-.252,1-.419,2.018-.593,3.033a15.146,15.146,0,0,1-.606,2.957c-.249.633-.44-.04-.635-.392a10.111,10.111,0,0,0-.787-1.217,9.962,9.962,0,0,0-1.941-1.945A11.521,11.521,0,0,0,79.5,55.5a17.266,17.266,0,0,0-4.669-.143,13.178,13.178,0,0,0-2.5.52c-6.04,1.9-9.562,8.271-11.488,14.013a35.571,35.571,0,0,0-1.681,8.376,40.449,40.449,0,0,0,.22,8.653A36.014,36.014,0,0,0,61.393,95a25.329,25.329,0,0,0,3.692,6.681c3.27,4.142,8.059,6.5,13.376,5.376,5.916-1.244,11.448-7.1,13.25-12.745.23-.72.243-2.379,1.166-1.986.481.2,1.271,2.328,1.608,2.835A14.728,14.728,0,0,0,99.021,99.7c2.893,1.747,12.325,3.29,11.44-2.6C110.159,95.1,108.662,94.491,107.031,93.8ZM87.125,89.332a8.331,8.331,0,0,1-4.608,3.676c-8.591,2.91-12.453-6.761-12.418-13.194.018-3.509,1.116-7.516,3.7-10.067a10.819,10.819,0,0,1,8.8-2.793,6,6,0,0,1,3.031,1.455c1.782,1.594,2.263,3.82,2.64,6.014a41.431,41.431,0,0,1,.657,6.527A15.8,15.8,0,0,1,87.125,89.332Z"
                fill={fill}
            />
            <path
                d="M144.421,86.531c-.9-7.164,6.483-17.488,6.237-25.784-.117-3.939-1.216-8.582-4.991-10.579A7.246,7.246,0,0,0,144,49.546a16.508,16.508,0,0,0-5.386-.172,17.9,17.9,0,0,0-5.824,1.568,26.016,26.016,0,0,0-8.6,7.126c-1.2,1.415-2.328,2.883-3.468,4.342.091-.116-.685-1.323-.776-1.479q-.445-.765-.961-1.488a16.611,16.611,0,0,0-2.324-2.638,9.659,9.659,0,0,0-5.719-2.737,7.376,7.376,0,0,0-3.453.671,5.485,5.485,0,0,0-2.441,1.825c-.73,1.229-.391,2.852-.02,4.138,1.153,3.989,3.474,7.577,5.192,11.331.18.393.355.789.52,1.188,1.771,4.257,3.91,8.588,4.159,12.893.17,2.942-1.623,7.1.832,7.486,7.989.954,5.764-12.772,7.484-19.963A16.019,16.019,0,0,1,133.728,61.7a1.824,1.824,0,0,1,2.585,1.755c.227,3.507-1.259,7.688-1.937,11.126a78.288,78.288,0,0,0-1.643,12.1c-.293,7.721,2,15.561,10.436,17.4,4.15.906,7.977,1.071,11.322-1.9,2.976-2.643,4.715-6.779,1.989-10.255C150.9,92.26,145.225,92.93,144.421,86.531Z"
                fill={fill}
            />
            <path
                d="M60.774,114.764c-.59-1.313-1.177-2.628-1.748-3.951-1.492-3.462-2.876-6.972-4.089-10.544C48.983,82.728,46.572,60.9,61.494,47.027a37.626,37.626,0,0,1,16.914-8.781c1.521-.37,6.336-.913,7.03-1.159,1.678-.6.964-4.956.685-6.2-1.45-6.476-9.885-8.881-15.658-8.33-5.986.571-11.164,4.164-15.138,8.487a47.922,47.922,0,0,0-8.6,13.562c-8.145,19.013-4.241,50.08,1.041,58.816-1.489-2.381-4.769-4.529-6.815-6.473-1.976-1.877-3.983-3.723-5.912-5.65A57.731,57.731,0,0,1,24.531,77.77C20.7,70.557,17.91,62.356,17.858,54.148c-.029-4.749.575-10.126,3.241-14.2-4.969-.946-9.68,1.9-13.308,5C3.577,48.546-.487,53.691.047,59.5c.591,6.424,4.5,12.558,7.563,18.083,5.932,10.709,14.266,19.769,23.636,27.6a111.456,111.456,0,0,0,16.287,11.246c3.855,2.2,8.913,4.791,13.336,2.654.4-.2,1.158-.479,1.277-.95.125-.5-.274-1.135-.468-1.563C61.4,115.958,61.052,115.378,60.774,114.764Z"
                fill={fill}
            />
            <path d="M7.791,44.946" fill={fill} />
            <path
                d="M167.28,66.51c2.04-3.913,7.259-9.81,5.71-14.594a7.188,7.188,0,0,0-11.143-3.162c-5.4,4.456-5.088,30.312-3.17,34.111,1.021,4.243,7.938,7.332,10.939,3.185a1.75,1.75,0,0,0-.265-2.461h0l-.035-.027a3.983,3.983,0,0,0-1.732-.21c-2.729-.161-3.345-5.914-3.256-7.912C164.467,72.273,165.838,69.275,167.28,66.51Z"
                fill={fill}
            />
            <path
                d="M291.726,67.85c-4.677-.667-8.018,3.9-11.228,5.406,2.768-7.411-3.456-12.191-4.988-13.723-21.435-14.167-51.728,23.317-36.6,37.842,6.541,5.14,16.874,6.412,24.121.832,1.019,11.687,23.945,13.514,24.118,2.078-27.916,7.189,5.087-16.211,9.15-24.952C297.936,71.817,295.906,68.443,291.726,67.85ZM272.873,78.4a36.587,36.587,0,0,1-5.69,8.773c-2.284,2.779-4.846,5.587-8.354,5.936a7.864,7.864,0,0,1-2.155-.093c-4.034-.724-6.165-3.017-5.937-7.245A17.967,17.967,0,0,1,256.076,74C262.482,67.756,277.819,66.512,272.873,78.4Z"
                fill={fill}
            />
            <path
                d="M238.05,103.619a21.449,21.449,0,0,1-3.981-1.923,22.521,22.521,0,0,1-6.186-5.578c-3.555-4.656-5.269-10.456-6.017-16.2a65.7,65.7,0,0,1-.46-10.389c30.989-6.622,46.037-40.689,41.17-49.071-4.42-7.613-13.253-5.3-21.08-.23a69.362,69.362,0,0,0-30.177,45.207,12.444,12.444,0,0,1-5.719.042c-.738-.161-1.851-.786-2.592-.536a1.344,1.344,0,0,0-.735,1.753h0c.009.021.018.042.028.063a3.273,3.273,0,0,0,1.641,1.5,18.358,18.358,0,0,0,6.34,2.089c-1.466,13.033,2.769,34.078,11.125,42.85a11.474,11.474,0,0,0,11.389,3.093c3.756-1.037,9.94-4.814,8.5-9.513A4.914,4.914,0,0,0,238.05,103.619Zm17.394-81.455c4.186,1.82-7.493,31.419-32.376,39.883,4.667-15.91,26.575-42.462,32.376-39.884Z"
                fill={fill}
            />
            <path
                d="M204.208,98.1a21.29,21.29,0,0,1-5.391-1.808,14.9,14.9,0,0,1-3.412-2.7,20.436,20.436,0,0,1-4.333-7.525A39.316,39.316,0,0,1,189.2,73.617a62.079,62.079,0,0,1,2.008-15.76,50.976,50.976,0,0,1,2.27-6.874c.354-.848,1.106-2.384,1.106-2.384s3.1-.121,4.23-.325c11.838-2.139,22.332-12,28.136-22.125C230.93,19.2,235.11,7.9,226.676,2.4c-13.238-8.62-25.25,8.079-30.466,16.7a115.671,115.671,0,0,0-7.479,15.043c-.929,2.2-1.814,4.425-2.683,6.652-.307.787-1.327,3.282-1.327,3.282s-1.626-.273-2.058-.379a23.111,23.111,0,0,1-3.051-.97c-2.76-1.1-6.1-2.994-6.912-6.1,11.01,3.7,16.084-14.924,4.157-17.049-7-.96-10.982,4.883-10.256,11.358a18.377,18.377,0,0,0,4.543,10.506,23.143,23.143,0,0,0,9.23,5.414c.747.264,2.259.739,2.259.739l.852.226s-.371,1.406-.471,1.753c-.62,2.154-1.222,4.312-1.782,6.483a105.862,105.862,0,0,0-2.686,13.678,70.63,70.63,0,0,0-.306,13.427c.587,8.4,2.151,19.117,8.93,24.945,3.11,2.675,9.048,4.589,12.839,2.238,3.312-2.053,4.169-6.421,5.374-9.814a1.869,1.869,0,0,0-.279-2.047A2.238,2.238,0,0,0,204.208,98.1Zm16.448-92.26C235.415,5.6,208.029,44,196.469,43.68,201.529,31.274,213.243,5.933,220.656,5.842Z"
                fill={fill}
            />
        </svg>
    );
}

export function SettingsIcon(props: { className?: string }) {
    const title = t("Settings");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 18"
            className={classNames("icon", "icon-settings", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            <path
                d="M6,18v-.5a.5.5,0,0,1,.5-.5h4a.5.5,0,0,1,.5.5V18H22v2H11v.5a.5.5,0,0,1-.5.5h-4a.5.5,0,0,1-.5-.5V20H2V18Zm9-7v-.5a.5.5,0,0,1,.5-.5h4a.5.5,0,0,1,.5.5V11h2v2H20v.5a.5.5,0,0,1-.5.5h-4a.5.5,0,0,1-.5-.5V13H2V11ZM4,4V3.5A.5.5,0,0,1,4.5,3h4a.5.5,0,0,1,.5.5V4H22V6H9v.5a.5.5,0,0,1-.5.5h-4A.5.5,0,0,1,4,6.5V6H2V4ZM5,4V6H8V4Zm11,7v2h3V11ZM7,18v2h3V18Z"
                transform="translate(-2 -3)"
                fill="currentColor"
            />
        </svg>
    );
}

export function SearchIcon(props: { className?: string }) {
    const title = t("Search");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 13.312 13.311"
            className={classNames("icon", "icon-search", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            <path
                d="M5.193,1.143A4.059,4.059,0,1,0,9.267,5.2,4.059,4.059,0,0,0,5.193,1.143h0M13.043,13.08a1.019,1.019,0,0,1-1.349-.054L8.125,9.456A5.182,5.182,0,1,1,9.477,8.113l3.559,3.559a1.033,1.033,0,0,1,0,1.409Z"
                transform="translate(-0.031 0.01)"
                fill="currentColor"
            />
        </svg>
    );
}

export function NotificationsIcon(props: { filled?: boolean; className?: string }) {
    const title = t("Notifications");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 18 20"
            className={classNames("icon", "icon-notifications", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            {!props.filled && (
                <path
                    d="M10,1.066A5.742,5.742,0,0,1,15,7v3q0,2.288,2.51,4.574A1.5,1.5,0,0,1,18,15.683V16.5A1.5,1.5,0,0,1,16.5,18H11a2,2,0,0,1-4,0H1.5A1.5,1.5,0,0,1,0,16.5v-.817a1.5,1.5,0,0,1,.49-1.109Q3,12.287,3,10V7A5.742,5.742,0,0,1,8,1.066V.958C8,.429,8.3,0,8.677,0h.646C9.7,0,10,.422,10,.951ZM1.5,16.5h15v-.817C14.541,13.9,13.5,12,13.5,10V7A4.262,4.262,0,0,0,9.49,2.5,4.45,4.45,0,0,0,9,2.5,4.262,4.262,0,0,0,4.5,6.51,4.45,4.45,0,0,0,4.5,7v3c0,2-1.041,3.9-3,5.682Z"
                    fill="currentColor"
                />
            )}
            {!!props.filled && (
                <path
                    d="M10,1.066A5.742,5.742,0,0,1,15,7v3q0,2.288,2.51,4.574A1.5,1.5,0,0,1,18,15.683V16.5A1.5,1.5,0,0,1,16.5,18H11a2,2,0,0,1-4,0H1.5A1.5,1.5,0,0,1,0,16.5v-.817a1.5,1.5,0,0,1,.49-1.109Q3,12.287,3,10V7A5.742,5.742,0,0,1,8,1.066V.958C8,.429,8.3,0,8.677,0h.646C9.7,0,10,.422,10,.951Z"
                    fill="currentColor"
                />
            )}
        </svg>
    );
}

export function MessagesIcon(props: { filled?: boolean; className?: string }) {
    const title = t("Messages");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20.051 14.016"
            className={classNames("icon", "icon-messages", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            {!props.filled && (
                <path
                    d="M8.7,10.991,4,7.339V17.5H21V7.339l-4.7,3.652.016.017S19.725,15.819,19.5,16s-4.346-3.966-4.346-3.966a.435.435,0,0,1-.06-.1l-1.827,1.42a1.249,1.249,0,0,1-1.534,0l-1.827-1.42C9.888,11.973,5.725,16.181,5.5,16S8.69,11,8.7,10.991ZM19.636,6.5H5.364L12.5,12.05ZM4,5H21a1.5,1.5,0,0,1,1.5,1.5v11A1.5,1.5,0,0,1,21,19H4a1.5,1.5,0,0,1-1.5-1.5V6.5A1.5,1.5,0,0,1,4,5Z"
                    transform="translate(-2.5 -4.984)"
                    fill="currentColor"
                />
            )}
            {!!props.filled && (
                <React.Fragment>
                    <path
                        d="M22.466,6.187l-6.162,4.8c.3.421,3.37,4.857,3.2,5.009-.216.189-4.377-4.057-4.376-4.083L13.3,13.337a1.418,1.418,0,0,1-.7.262,1.473,1.473,0,0,1-.831-.262l-1.827-1.42S8.922,12.954,8.1,13.753a20.909,20.909,0,0,1-2.49,2.217c-.038.017-.068.025-.081.014-.211-.169,2.83-4.489,3.2-5.009,0,0-6.1-4.816-6.184-4.816a.865.865,0,0,0-.042.29c0,.076.03,11.035.03,11.035a1.5,1.5,0,0,0,1.5,1.5h17a1.5,1.5,0,0,0,1.5-1.5S22.606,6.41,22.466,6.187Z"
                        transform="translate(-2.5 -4.984)"
                        fill="currentColor"
                    />
                    <path
                        d="M21.463,5.08a1.694,1.694,0,0,0-.433-.1h-17a1.861,1.861,0,0,0-.5.1L12.5,12.05Z"
                        transform="translate(-2.5 -4.984)"
                        fill="currentColor"
                    />
                </React.Fragment>
            )}
        </svg>
    );
}

export function UserIcon(props: { filled?: boolean; className?: string }) {
    const title = t("Me");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            className={classNames("icon", "icon-user", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            {!!props.filled && (
                <path
                    fill="currentColor"
                    d="M10,0A10,10,0,1,0,20,10,10,10,0,0,0,10,0Zm0,4.516a3.549,3.549,0,1,1-3.548,3.55h0A3.547,3.547,0,0,1,10,4.516ZM10,18.5a8.472,8.472,0,0,1-6.349-2.862,3.855,3.855,0,0,1,3.768-3.057c.831,0,1.226.459,2.581.459s1.754-.459,2.581-.459a3.855,3.855,0,0,1,3.768,3.057A8.472,8.472,0,0,1,10,18.5Z"
                />
            )}
            {!props.filled && (
                <path
                    fill="currentColor"
                    d="M5,16.875a8.5,8.5,0,0,0,10,0v-.208A2.59,2.59,0,0,0,12.5,14a10.754,10.754,0,0,1-2.5.475A10.754,10.754,0,0,1,7.5,14,2.59,2.59,0,0,0,5,16.667Zm11.352-1.226a8.5,8.5,0,1,0-12.7,0,3.856,3.856,0,0,1,3.771-3.068c.831,0,1.226.459,2.581.459s1.754-.459,2.581-.459A3.856,3.856,0,0,1,16.352,15.649ZM10,0A10,10,0,1,1,0,10,10,10,0,0,1,10,0Zm0,4.516a3.549,3.549,0,1,1-3.548,3.55h0A3.547,3.547,0,0,1,10,4.516Zm0,5.807a2.259,2.259,0,1,0-2.258-2.26v0A2.259,2.259,0,0,0,10,10.323Z"
                />
            )}
        </svg>
    );
}

export function NoUserPhotoIcon(props: { className?: string }) {
    const title = t("User");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            className={classNames("icon", "icon-noUserPhoto", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            <path
                d="M12.046,12.907c-2.225,0-4.03-2.218-4.03-4.954C8.016,4.16,9.82,3,12.046,3s4.03,1.16,4.03,4.953C16.076,10.689,14.271,12.907,12.046,12.907Zm8.9,6.452a17.94,17.94,0,0,1-.194,4.2A1.025,1.025,0,0,1,19.9,24H3.96a1.024,1.024,0,0,1-.852-.443,17.956,17.956,0,0,1,.04-4.2l2.033-4.39a1,1,0,0,1,.46-.469L8.8,12.926a.211.211,0,0,1,.217.017,5.149,5.149,0,0,0,6.068,0,.211.211,0,0,1,.216-.017L18.452,14.5a1,1,0,0,1,.46.469Z"
                fill="currentColor"
            />
        </svg>
    );
}

export function UserWarningIcon(props: { className?: string }) {
    const title = t("Warning");
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 40 40"
            className={classNames("icon", "icon-userWaning", props.className)}
            aria-hidden="true"
        >
            <title>{title}</title>
            <rect width="40" height="40" fill="#d0021b" opacity="0.31" />
            <path
                d="M32.707,25.862a2.167,2.167,0,0,1-1.876,3.249H9.169a2.168,2.168,0,0,1-1.877-3.249L18.123,7.083a2.168,2.168,0,0,1,3.754,0Z"
                fill="#d0021b"
            />
            <path
                d="M20,20.979a2.077,2.077,0,1,0,2.076,2.077A2.076,2.076,0,0,0,20,20.979Zm-1.971-7.463.335,6.139a.541.541,0,0,0,.54.512H21.1a.543.543,0,0,0,.541-.512l.334-6.139a.542.542,0,0,0-.54-.572H18.569A.542.542,0,0,0,18.029,13.516Z"
                fill="#fff"
            />
        </svg>
    );
}
