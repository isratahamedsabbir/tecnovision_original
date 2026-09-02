<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>New Customer Query Received</title>

    <style>
        @media only screen and (max-width: 600px) {
            .email-container { 
                width: 100% !important;
            }

            .mobile-padding {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .header-title {
                font-size: 28px !important;
                line-height: 34px !important;
            }

            .header-subtitle {
                font-size: 16px !important;
            }

            .content-padding {
                padding: 25px 20px !important;
            }

            .details-padding {
                padding: 20px !important;
            }

            .detail-label {
                display: block !important;
                width: 100% !important;
                padding-bottom: 5px !important;
            }

            .detail-value {
                display: block !important;
                width: 100% !important;
                padding-bottom: 18px !important;
            }

            .footer-text {
                text-align: center !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#ffffff; font-family:Arial, Helvetica, sans-serif; color:#444444;">

    <!-- Main Wrapper -->
    <table
        role="presentation"
        width="100%"
        border="0"
        cellspacing="0"
        cellpadding="0"
        style="width:100%; margin:0; padding:0; background-color:#ffffff;"
    >
        <tr>
            <td align="center" style="padding:35px 15px;">

                <!-- Email Container -->
                <!--[if mso]>
                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0">
                <tr>
                <td>
                <![endif]-->

                <table
                    role="presentation"
                    width="600"
                    border="0"
                    cellspacing="0"
                    cellpadding="0"
                    class="email-container"
                    style="width:100%; max-width:600px; margin:0 auto; background-color:#ffffff;"
                > 

                    <!-- Header -->
                    <tr>
                        <td
                            align="center"
                            style="background-color:#363636; padding:15px 15px 18px 15px;"
                        > 
                            <a
                            target="_blank"
                                href="{{ url('/') }}"
                                class="header-title"
                                style="font-family:Arial, Helvetica, sans-serif; font-size:32px; line-height:38px; font-weight:700; color:#ffffff; margin:0;"
                            >
                                <img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" alt="{{ get_setting('site_name') }}" style="max-width: 100px; height: auto;">
                            </a>

                            <div
                                class="header-subtitle"
                                style="
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 16px;
                                    line-height: 22px;
                                    font-weight: 400;
                                    color: #ffffff;
                                    margin-top: 5px;
                                "
                            >
                                New Customer Query Received
                            </div>
                        </td>
                    </tr>


                    <!-- Content -->
                    <tr>
                        <td
                            class="content-padding"
                            style="padding:15px 0 0 0;"
                        >

                            <!-- Greeting -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                            >
                                <tr>
                                    <td
                                        style="font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:20px; font-weight:600; color:#333333; padding-bottom:10px;"
                                    >
                                        Hello Team,
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="font-family: Arial, Helvetica, sans-serif;
                                        font-size: 15px;
                                        line-height: 23px;
                                        font-weight: 400;
                                        color: #555555;
                                        padding-bottom: 15px;"
                                    >
                                        You have received a new query from a customer through the {{ get_setting('site_name') }} website contact form.
                                        The details are given below:
                                    </td>
                                </tr>
                            </table>


                            <!-- Customer Details Box -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                style="width:100%; background-color:#f1f5fb; border:1px solid #cbd3dc;"
                            >
                                <tr>
                                    <td
                                        class="details-padding"
                                        style="padding:20px 20px;"
                                    > 

                                        <!-- Full Name -->
                                        <table
                                            role="presentation"
                                            width="100%"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="width:100%;"
                                        >
                                            <tr>
                                                <td
                                                    class="detail-label"
                                                    width="220"
                                                    valign="top"
                                                    style="width:220px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:600; color:#303030; padding-bottom:12px;"
                                                >
                                                    Full Name:
                                                </td>

                                                <td
                                                    class="detail-value"
                                                    valign="top"
                                                    style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:400; color:#555555; padding-bottom:12px;"
                                                >
                                                    {{ $name }}
                                                </td>
                                            </tr>
                                        </table>


                                        <!-- Email -->
                                        <table
                                            role="presentation"
                                            width="100%"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="width:100%;"
                                        >
                                            <tr>
                                                <td
                                                    class="detail-label"
                                                    width="220"
                                                    valign="top"
                                                    style="width:220px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:600; color:#303030; padding-bottom:12px;"
                                                >
                                                    Email Address:
                                                </td>

                                                <td
                                                    class="detail-value"
                                                    valign="top"
                                                    style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:400; color:#555555; padding-bottom:12px; word-break:break-word;"
                                                >
                                                    {{ $email }}
                                                </td>
                                            </tr>
                                        </table>


                                        <!-- Phone -->
                                        <table
                                            role="presentation"
                                            width="100%"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="width:100%;"
                                        > 
                                            <tr>
                                                <td
                                                    class="detail-label"
                                                    width="220"
                                                    valign="top"
                                                    style="width:220px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:600; color:#303030; padding-bottom:12px;"
                                                >
                                                    Phone Number:
                                                </td>

                                                <td
                                                    class="detail-value"
                                                    valign="top"
                                                    style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:400; color:#555555; padding-bottom:12px;"
                                                >
                                                    {{ $phone }}
                                                </td>
                                            </tr> 
                                        </table>


                                        <!-- Message -->
                                        <table
                                            role="presentation"
                                            width="100%"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="width:100%;"
                                        >
                                            <tr>
                                                <td
                                                    class="detail-label"
                                                    width="220"
                                                    valign="top"
                                                    style="width:220px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:600; color:#303030;"
                                                >
                                                    Message:
                                                </td>

                                                <td
                                                    class="detail-value"
                                                    valign="top"
                                                    style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:400; color:#555555; word-break:break-word;"
                                                >
                                                    {!! $content !!}
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>


                            <!-- Bottom Message -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                style="width:100%;"
                            >
                                <tr>
                                    <td
                                        style="padding:20px 0 0 0; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; font-weight:400; color:#555555;"
                                    >
                                        Please review the query and get in touch with the customer as soon as possible.
                                    </td>
                                </tr>
                            </table>


                            <!-- CTA -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                style="width:100%;"
                            > 
                                <tr>  
                                    <td
                                        align="center"
                                        style="padding:22px 0 22px 0;"
                                    >
                                        <a
                                            href="{{ url('/') }}"
                                            target="_blank"
                                            style="font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:22px; font-weight:400; color:#045b30; text-decoration:underline;"
                                        >
                                            VISIT WEBSITE
                                        </a>
                                    </td> 
                                </tr>
                            </table> 


                            <!-- Divider -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                            >
                                <tr>
                                    <td
                                        style="border-top:1px dashed #222222; font-size:0; line-height:0;"
                                    >
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>


                            <!-- Footer -->
                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                            >
                                <tr>
                                    <td
                                        class="footer-text"
                                        style="padding:22px 0 0 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:24px; color:#999999;"
                                    > 
                                        <span style="font-weight:400;">
                                            {{ get_setting('site_name') }}
                                        </span>

                                        <span style="padding:0 8px; color:#999999;">
                                            |
                                        </span>

                                        <span>
                                            Phone: {{ get_setting('contact_phone') }}
                                        </span>

                                        <span style="padding:0 8px; color:#999999;">
                                            |
                                        </span>

                                        <span>
                                            Email: {{ get_setting('contact_email') }}
                                        </span>
                                    </td>
                                </tr>
                            </table> 

                        </td>
                    </tr>

                </table>

                <!--[if mso]>
                </td>
                </tr>
                </table>
                <![endif]-->

            </td>
        </tr>
    </table>

</body>
</html>
