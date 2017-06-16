<?php

namespace Sitepod\Controller;

class SiteMap
{

    public function parse()
    {
        global $SETTINGS;
        $FILE = parseFilesystem();

        // check for timeout
        if ($SETTINGS[PSNG_TIMEOUT_ACTION] != '') {
            return;
        }
        // if no timeout, print result or write it
        if ($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) {
            displaySitemapEdit($FILE);
        } else {
            writeSitemap($FILE);
        }
    }

    public function resetSettings()
    {
        (new Home())->viewSetup(TRUE);
    }

    public function getSettings()
    {
        getSettings();
        $this->parse();
    }

    public function writeSiteMapUserInput()
    {
        writeSitemapUserinput();
    }

    public function submitPageToGoogle()
    {
        submitPageToGoogle();
    }
}