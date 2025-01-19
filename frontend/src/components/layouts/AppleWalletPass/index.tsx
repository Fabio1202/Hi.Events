import "../../../styles/widget/default.scss";
import React from "react";
import {Event} from "../../../types.ts";
import {HomepageInfoMessage} from "../../common/HomepageInfoMessage";
import {t} from "@lingui/macro";
import classes from "../EventHomepage/EventHomepage.module.scss";

interface AppleWalletPassProps {
    colors?: {
        background?: string;
        primary?: string;
        label?: string;
    };
    event?: Event;
}

const EventHomepage = ({colors, ...loaderData}: AppleWalletPassProps) => {
    const {event} = loaderData;

    const styleOverrides = {
        "--apple-wallet-body-background-color":
            colors?.label || event?.settings?.homepage_body_background_color,
        "--homepage-background-color":
            colors?.background || event?.settings?.homepage_background_color,
        "--homepage-primary-color":
            colors?.primary || event?.settings?.homepage_primary_color
    } as React.CSSProperties;

    if (!event) {
        return <HomepageInfoMessage message={t`This event is not available.`}/>;
    }

    return (
        <div style={styleOverrides} key={`${event.id}`}>
            <div className={classes.background}
                 style={{backgroundColor: 'white'}}
            />

            <div className={classes.styleContainer}>
                <span>Test</span>
            </div>
        </div>
    );
};

export default EventHomepage;
