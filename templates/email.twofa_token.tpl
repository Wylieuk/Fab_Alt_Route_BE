<!DOCTYPE html>
    <html>
        <head>

    <style type="text/css">
      body,
      #bodyTable{
        background-color:#ffffff;
        display:block;
      }
      #bodyCell{
        border-top:4px solid #003363;
        display: block;
      }
      #templatePreheader{
        background-color:#9ac7e2;
      }
      .preheaderContent{
        color:#ffffff;
        font-family:Arial;
        font-size:10px;
        line-height:125%;
        text-align:center;
        width:100%;
        background-color: #9ac7e2;
        padding:5px;
        max-width: 640px;
      }
      .preheaderContent a:link, .preheaderContent a:visited, /* Yahoo! Mail Override */ .preheaderContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#003363;
        font-weight:normal;
        text-decoration:none;
      }
      #templateHeader{
        background-color:#ffffff;
      }
      .headerContent{
        color:#003363;
        font-family:Helvetica;
        font-size:26px;
        font-weight:bold;
        line-height:100%;
        text-align:left;
        background-color: #ffffff;
        vertical-align:middle;
        width: 100%;
        max-width: 640px;
        padding: 20px;
      }
      .headerContent a:link, .headerContent a:visited, /* Yahoo! Mail Override */ .headerContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:underline;
      }
      #headerImage{
        height:auto;
        max-width:640px;
        width: 100%;
      }
      #templatesubheader{
        background-color:#003363;
      }
      .subheaderContent{
        color:#ffffff;
        font-family:Helvetica;
        font-size:12px;
        font-weight:normal;
        line-height:125%;
        padding-right:20px;
        padding-top:10px;
        padding-bottom:10px;
        text-align:right;
        background-color: #003363;
        vertical-align:middle;
        width: 100%;
        max-width: 600px;
      }
      .subheaderContent a:link, .subheaderContent a:visited, /* Yahoo! Mail Override */ .subheaderContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#FFFFFF;
        font-weight:bold;
        text-decoration:none!important;
      }
      #templateBody{
        background-color:#FFFFFF;
      }
      .bodyContent{
        color: #505050;
        font-family: Arial;
        font-size: 12px;
        line-height: 125%;
        text-align: left;
        padding-left: 20px;
        width:100%;
        max-width: 640px;
      }
      .bodyContent a:link, .bodyContent a:visited, /* Yahoo! Mail Override */ .bodyContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:none;
      }
      .bodyContent img{
        display:block;
        height:auto;
        max-width:640px;
      }
      /* ========== Column Styles ========== */
      .templateColumnContainer{
        width:200px;
      }
      #templateColumns{
        border-top:none;
        border-bottom:none;
      }
      #white {
        background-color:#FFFFFF;
      }
      .leftColumnContent{
        color:#505050;
        font-family:Arial;
        font-size:14px;
        line-height:150%;
        text-align:left;
      }
      .leftColumnContent a:link, .leftColumnContent a:visited, /* Yahoo! Mail Override */ .leftColumnContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:underline;
      }
      .centerColumnContent{
        color:#505050;
        font-family:Arial;
        font-size:14px;
        line-height:125%;
        text-align:left;
      }
      .centerColumnContent a:link, .centerColumnContent a:visited, /* Yahoo! Mail Override */ .centerColumnContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:underline;
      }
      .rightColumnContent{
        color:#505050;
        font-family:Helvetica;
        font-size:14px;
        line-height:125%;
        text-align:left;
      }
      .rightColumnContent a:link, .rightColumnContent a:visited, /* Yahoo! Mail Override */ .rightColumnContent a .yshortcuts /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:underline;
      }
      .leftColumnContent img, .rightColumnContent img{
        display:block;
        height:auto;
      }
      /* ========== Footer Styles ========== */
      #templateFooter{
        background-color:#003363;
      }
      .footerContent{
        color:#FFFFFF;
        font-family:Helvetica;
        font-size:10px;
        line-height:125%;
        padding-top:10px;
        padding-right:20px;
        padding-bottom:10px;
        padding-left:20px;
        text-align:center;
        background-color:#003363;
        width: 100%;
        max-width: 640px;
      }
      .footerContent a:link, .footerContent a:visited, /* Yahoo! Mail Override */ .footerContent a .yshortcuts, .footerContent a span /* Yahoo! Mail Override */{
        color:#9ac7e2;
        font-weight:normal;
        text-decoration:none;
      }
      body {
        margin: 0 !important;
        padding: 0 !important;
        -webkit-text-size-adjust: 100% !important;
        -ms-text-size-adjust: 100% !important;
        -webkit-font-smoothing: antialiased !important;
      }
      img {
        border: 0 !important;
        outline: none !important;
        display: block;
        width:100%;
      }
      p {
        font-family: Helvetica;
        font-size:16px;
        font-weight:normal;
        font-style:normal;
        line-height: 125%;
        color:#505050;
        margin: 0px !important;
        text-align:center;
      }
      table {
        border-collapse: collapse;
        mso-table-lspace: 0px;
        mso-table-rspace: 0px;
      }
      td, a, span {
        border-collapse: collapse;
        mso-line-height-rule: exactly;
      }
      h1{
        color:#FFFFFF !important;
        display:block;
        font-family: Helvetica;
        font-size:26px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        letter-spacing:normal;
        text-align:center;
        padding-top: 20px;
        padding-left: 20px;
        margin: 0px;
      }
      h2{
        color:#003363 !important;
        display:block;
        font-family: Helvetica;
        font-size:26px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        letter-spacing:normal;
        text-align:center;
        padding-top: 20px;
        padding-left: 20px;
        margin: 0px;
      }
      h3{
        color:#9ac7e2 !important;
        display:block;
        font-family: Helvetica;
        font-size:18px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        letter-spacing:normal;
        text-align:center;
        padding-top: 5px;
        padding-left: 20px;
        margin: 0px;
      }
      h4{
        color:#003363 #BC4749;
        display:block;
        font-family: Helvetica;
        font-size:18px;
        font-style:normal;
        font-weight:normal;
        line-height:125%;
        letter-spacing:normal;
        margin-top:0;
        margin-right:0;
        margin-bottom:5px;
        margin-left:0;
        text-align:center;
      }
      h5{
        color:#9ac7e2 !important;
        display:block;
        font-family: Helvetica;
        font-size:18px;
        font-style:normal;
        font-weight:normal;
        line-height:125%;
        letter-spacing:normal;
        margin-top:0;
        margin-right:0;
        margin-bottom:5px;
        margin-left:0;
        text-align:center;
      }
      h6{
        color:#505050 !important;
        display:block;
        font-family: Helvetica;
        font-size:18px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        letter-spacing:normal;
        text-align:center;
        padding-top: 10px;
        margin: 0px;
        padding-bottom: 5px;
      }
      .ExternalClass * {
        line-height: 100%;
      }
      .hl_white a {
        text-decoration: none;
        color: #ffffff;
      }
      .hl_grey a {
        text-decoration: none;
        color: #808080;
      }
      .hl_blue a {
        text-decoration: none;
        color: #003363;
      }
      .hl_light a {
        text-decoration: none;
        !important;
        color: #9ac7e2;
      }
      .em_br1 {
        display: none;
      }
      .article1{
        width:100%;
        border-top: 3px #003363 solid;
        background-color:#9ac7e2;
        text-align: left;
        color: #505050;
      }
      .article2{
        width:100%;
        border-top: 3px #9ac7e2 solid;
        background-color:#9ac7e2;
        text-align: left;
        color: #505050;
      }
      .article3{
        width:100%;
        background-color:#FFFFFF;
        text-align: left;
        color: #505050;
      }
      .button1{
        width: 200px;
        color:#FFFFFF !important;
        display:block;
        font-family: Helvetica;
        font-size:14px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        text-align:center;
        border: 2px #003363 solid;
        border-radius: 30px;
        background-color: #003363;
        height: 35px;
        vertical-align:middle;
        padding-top:5px;
      }
      .button2{
        width: 200px;
        color:#505050 !important;
        display:block;
        font-family: Helvetica;
        font-size:14px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        text-align:center;
        border: 2px #003363 solid;
        border-radius: 30px;
        background-color: #9ac7e2;
        height: 35px;
        vertical-align:middle;
      }
      .button3{
        width: 150px;
        color:#505050 !important;
        display:block;
        font-family: Helvetica;
        font-size:14px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        text-align:center;
        border: 2px #003363 solid;
        border-radius: 30px;
        background-color: #9ac7e2;
        height: 35px;
        vertical-align:middle;
      }
      .button4{
        width: 150px;
        color:#003363 !important;
        display:block;
        font-family: Helvetica;
        font-size:14px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        text-align:center;
        border: 2px #9ac7e2 solid;
        border-radius: 30px;
        background-color: #9ac7e2;
        height: 35px;
        vertical-align:middle;
      }
      .button5{
        width: 150px;
        color:#003363 !important;
        display:block;
        font-family: Helvetica;
        font-size:14px;
        font-style:normal;
        font-weight:bold;
        line-height:125%;
        text-align:center;
        border: 2px #9ac7e2 solid;
        border-radius: 30px;
        background-color: #ffffff;
        height: 35px;
        vertical-align:middle;
      }
      @media only screen and (max-width:320px) {
        table[class=em_main_table] {
          width: 100% !important;
          padding:5px !important;
        }
        table[class=em_wrapper] {
          width: 100% !important;
          padding: 5px;
        }
        td[class=em_hide], br[class=em_hide] {
          display: none !important;
        }
        td[class=em_align_center] {
          text-align: center !important;
          align-content: center !important;
          align-items: center !important;
        }
        td[class=em_pad_top] {
          padding-top: 20px !important;
        }
        td[class=em_side_space] {
          padding-left: 20px !important;
          padding-right: 20px !important;
        }
        td[class=em_bg_center] {
          background-position: center !important;
        }
        td[class=em_height_80] {
          height: 200px !important;
        }
        img[class=em_full_width] {
          width: 100% !important;
          height: auto !important;
          max-width: 100% !important;
          padding:5px;
          align-content: center;
        }
        td[class=em_pad_btm] {
          padding-bottom: 28px !important;
        }
        td[class=em_pad_none] {
          padding: 0px !important;
        }
        td[class=em_width_178] {
          width: 178px !important;
        }
      }
      @media only screen and (min-width:320px) and (max-width:650px) {
        table[class=em_main_table] {
          width: 100% !important;
        }
        table[class=em_wrapper] {
          width: 100% !important;
        }
        td[class=em_hide], br[class=em_hide] {
          display: none !important;
        }
        td[class=em_align_center] {
          text-align: center !important;
          align-content: center !important;
          align-items: center !important;
        }
        td[class=em_pad_top] {
          padding-top: 20px !important;
        }
        td[class=em_side_space] {
          padding-left: 20px !important;
          padding-right: 20px !important;
        }
        td[class=em_bg_center] {
          background-position: center !important;
        }
        img[class=em_full_width] {
          width: auto !important;
          height: auto !important;
          max-width: 100% !important;
        }
        td[class=em_pad_btm] {
          padding-bottom: 28px !important;
        }
    </style>

    </head>
    <body>
    
      <!-- ET Tracking Code--> 
      <!-- `date "+ET%m.%y"` --> 
      <!--Full width table start-->
      <table width="640" border="0" cellspacing="0" cellpadding="0" align="center" style="max-width:640px; background-color: #ffffff;" class="em_main_table">
        <tr>
          <td>
            <table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" style="max-width: 640px; background-color: #FFFFFF;">
              <!-- ===== HEADER SECTION ===== -->
              <tr>
                <td>
                  <table align="center" class="em_main_table" border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tbody>
                      <tr>
                        <td class="headerContent" width="100%" style="max-width:640px; padding: 10px;" bgcolor="#ffffff">
                          <table width="100%" class="em_wrapper">
                            <tbody>
                              <tr>
                                <td width="100%" height="60" align="center" cellpadding="10">
                                </td>
                                <td width="40%" class="em_hide">&nbsp;
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <!-- ===== //HEADER SECTION ===== -->
              <!-- ===== BODY ===== -->
              <tr>
                <td>
                  <table align="left" class="em_main_table" border="0" cellspacing="0" cellpadding="0" width="100%" style="max-width: 640px; background-color: #FFFFFF; padding: 0px;">
                    <tbody>
                      <tr>
                        <td style="max-width: 640px;">
                          <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="background-color: transparent; border: 0px; min-width: 100%; " class="stylingblock-content-wrapper"><tr><td style="padding: 0px; " class="stylingblock-content-wrapper camarker-inner"><table align="center" cellpadding="0" cellspacing="0" style="max-width: 640px; table-layout: fixed!important;" width="100%">
 
  <tr>
   <td align="center">
   
 
  <tr>
   <td style="font-size:14px; text-align: center; font-family:Helvetica; color:#505050; line-height:120%">
   <span class="preheader" style="display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0;">Use this to log into your Commuter Rewards account.&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
   <div style="display:none; white-space:nowrap; font:15px courier; color:#ffffff;">
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    We take your account&rsquo;s security seriously, that&rsquo;s why we ask for confirmation when trying to access your&nbsp;account.<br>
    <br>
    To confirm it's you, simply copy the below authentication code into the required&nbsp;box:<br>
    <br>
    &nbsp;<div style="font-size:22px; font-weight: bold; text-align: center; font-family:Helvetica; color:#505050;">
     {$template_vars->token->code}</div><br>
    <br>
    If you did not login, you can safely ignore this email.</td></tr></table></td></tr></table>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          
                        </td>
                      </tr>
                      <tr>
                        <td>
                          
                        </td>
                      </tr>
                      <tr>
                        <td>
                          
                        </td>
                      </tr>

                    </tbody>
                  </table>
                </td>
              </tr>
              <!-- ===== //BODY ===== --> 
              <!-- ===== FOOTER ===== -->
              <tr>
                <td>
                  <table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#003363" style="max-width: 640px;" class="em_main_table">
                    <tbody>
                      <tr>
                        <td class="footerContent">
                          <table width="100%" class="em_wrapper" cellpadding="10" cellspacing="0">
                            <tbody>
                              <tr>
                                <td style="padding: 20px;" align="center">
                                </td>
                              </tr>
                              <tr>
                                <td align="center">
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              </tbody>
      </table>
        </td>
      </tr>
    </table>
  <!-- ===== //FOOTER ===== -->
  <!--Full width table End-->
  <custom name="opencounter" type="tracking">

  </body>
  </html>
