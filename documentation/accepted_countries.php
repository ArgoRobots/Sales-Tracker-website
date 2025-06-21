<?php
session_start();
require_once '../db_connect.php';
require_once '../community/formatting/formatting_functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Accepted Country Names - Argo Community</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="../resources/styles/help.css">
    <link rel="stylesheet" href="accepted-countries.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/link.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/header/dark.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="countries-container">
        <a href="index.html#spreadsheets" class="link-no-underline back-link">← Back to Documentation</a>

        <div class="countries-header">
            <h1>Accepted Country Names</h1>
            <p>When importing spreadsheet data, country names must match the system's country list or use recognized variants. All variants listed below are automatically converted to the standardized country name.</p>
            <input type="text" class="search-box" id="countrySearch" placeholder="Search countries...">
        </div>

        <div class="countries-category">
            <h3>Most Common Countries</h3>
            <div class="country-group">
                <h4>United States</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">United States</span> (primary)</li>
                    <li>US</li>
                    <li>USA</li>
                    <li>U.S.</li>
                    <li>U.S.A.</li>
                    <li>United States of America</li>
                    <li>America</li>
                    <li>States</li>
                    <li>The States</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>China</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">China</span> (primary)</li>
                    <li>CN</li>
                    <li>CHN</li>
                    <li>PRC</li>
                    <li>People's Republic of China</li>
                    <li>Mainland China</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Germany</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Germany</span> (primary)</li>
                    <li>DE</li>
                    <li>DEU</li>
                    <li>Deutschland</li>
                    <li>Federal Republic of Germany</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>United Kingdom</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">United Kingdom</span> (primary)</li>
                    <li>GB</li>
                    <li>GBR</li>
                    <li>UK</li>
                    <li>U.K.</li>
                    <li>Great Britain</li>
                    <li>Britain</li>
                    <li>England</li>
                    <li>Scotland</li>
                    <li>Wales</li>
                    <li>Northern Ireland</li>
                </ul>
            </div>
        </div>

        <div class="countries-category">
            <h3>North America</h3>
            <div class="country-group">
                <h4>Canada</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Canada</span> (primary)</li>
                    <li>CA</li>
                    <li>CAN</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Mexico</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Mexico</span> (primary)</li>
                    <li>MX</li>
                    <li>MEX</li>
                    <li>United Mexican States</li>
                </ul>
            </div>
        </div>

        <div class="countries-category">
            <h3>Europe</h3>
            <div class="country-group">
                <h4>France</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">France</span> (primary)</li>
                    <li>FR</li>
                    <li>FRA</li>
                    <li>French Republic</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Italy</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Italy</span> (primary)</li>
                    <li>IT</li>
                    <li>ITA</li>
                    <li>Italian Republic</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Spain</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Spain</span> (primary)</li>
                    <li>ES</li>
                    <li>ESP</li>
                    <li>Kingdom of Spain</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Netherlands</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Netherlands</span> (primary)</li>
                    <li>NL</li>
                    <li>NLD</li>
                    <li>Holland</li>
                    <li>Kingdom of the Netherlands</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Switzerland</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Switzerland</span> (primary)</li>
                    <li>CH</li>
                    <li>CHE</li>
                    <li>Swiss Confederation</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Russia</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Russia</span> (primary)</li>
                    <li>RU</li>
                    <li>RUS</li>
                    <li>Russian Federation</li>
                    <li>USSR</li>
                    <li>Soviet Union</li>
                </ul>
            </div>
        </div>

        <div class="countries-category">
            <h3>Asia</h3>
            <div class="country-group">
                <h4>Japan</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Japan</span> (primary)</li>
                    <li>JP</li>
                    <li>JPN</li>
                    <li>Nippon</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>South Korea</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">South Korea</span> (primary)</li>
                    <li>KR</li>
                    <li>KOR</li>
                    <li>Korea</li>
                    <li>Republic of Korea</li>
                    <li>ROK</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>India</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">India</span> (primary)</li>
                    <li>IN</li>
                    <li>IND</li>
                    <li>Republic of India</li>
                    <li>Bharat</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>Singapore</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Singapore</span> (primary)</li>
                    <li>SG</li>
                    <li>SGP</li>
                    <li>Republic of Singapore</li>
                </ul>
            </div>
        </div>

        <div class="countries-category">
            <h3>Oceania</h3>
            <div class="country-group">
                <h4>Australia</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">Australia</span> (primary)</li>
                    <li>AU</li>
                    <li>AUS</li>
                    <li>Commonwealth of Australia</li>
                </ul>
            </div>
            <div class="country-group">
                <h4>New Zealand</h4>
                <ul class="countries-list">
                    <li><span class="primary-name">New Zealand</span> (primary)</li>
                    <li>NZ</li>
                    <li>NZL</li>
                    <li>Aotearoa</li>
                </ul>
            </div>
        </div>

        <div class="countries-category">
            <h3>All Other Countries (A-Z)</h3>
            <div class="alphabetical-grid">
                <div class="alphabet-section">
                    <h4>A</h4>
                    <div class="country-group">
                        <h5>Afghanistan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Afghanistan</span> (primary)</li>
                            <li>AF</li>
                            <li>AFG</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Albania</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Albania</span> (primary)</li>
                            <li>AL</li>
                            <li>ALB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Algeria</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Algeria</span> (primary)</li>
                            <li>DZ</li>
                            <li>DZA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Andorra</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Andorra</span> (primary)</li>
                            <li>AD</li>
                            <li>AND</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Angola</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Angola</span> (primary)</li>
                            <li>AO</li>
                            <li>AGO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Antigua and Barbuda</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Antigua and Barbuda</span> (primary)</li>
                            <li>AG</li>
                            <li>ATG</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Argentina</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Argentina</span> (primary)</li>
                            <li>AR</li>
                            <li>ARG</li>
                            <li>Argentine Republic</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Armenia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Armenia</span> (primary)</li>
                            <li>AM</li>
                            <li>ARM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Austria</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Austria</span> (primary)</li>
                            <li>AT</li>
                            <li>AUT</li>
                            <li>Republic of Austria</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Azerbaijan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Azerbaijan</span> (primary)</li>
                            <li>AZ</li>
                            <li>AZE</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>B</h4>
                    <div class="country-group">
                        <h5>Bahamas</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bahamas</span> (primary)</li>
                            <li>BS</li>
                            <li>BHS</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bahrain</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bahrain</span> (primary)</li>
                            <li>BH</li>
                            <li>BHR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bangladesh</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bangladesh</span> (primary)</li>
                            <li>BD</li>
                            <li>BGD</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Barbados</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Barbados</span> (primary)</li>
                            <li>BB</li>
                            <li>BRB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Belarus</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Belarus</span> (primary)</li>
                            <li>BY</li>
                            <li>BLR</li>
                            <li>Republic of Belarus</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Belgium</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Belgium</span> (primary)</li>
                            <li>BE</li>
                            <li>BEL</li>
                            <li>Kingdom of Belgium</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Belize</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Belize</span> (primary)</li>
                            <li>BZ</li>
                            <li>BLZ</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Benin</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Benin</span> (primary)</li>
                            <li>BJ</li>
                            <li>BEN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bhutan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bhutan</span> (primary)</li>
                            <li>BT</li>
                            <li>BTN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bolivia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bolivia</span> (primary)</li>
                            <li>BO</li>
                            <li>BOL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bosnia and Herzegovina</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bosnia and Herzegovina</span> (primary)</li>
                            <li>BA</li>
                            <li>BIH</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Botswana</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Botswana</span> (primary)</li>
                            <li>BW</li>
                            <li>BWA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Brazil</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Brazil</span> (primary)</li>
                            <li>BR</li>
                            <li>BRA</li>
                            <li>Federative Republic of Brazil</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Brunei</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Brunei</span> (primary)</li>
                            <li>BN</li>
                            <li>BRN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Bulgaria</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Bulgaria</span> (primary)</li>
                            <li>BG</li>
                            <li>BGR</li>
                            <li>Republic of Bulgaria</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Burkina Faso</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Burkina Faso</span> (primary)</li>
                            <li>BF</li>
                            <li>BFA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Burundi</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Burundi</span> (primary)</li>
                            <li>BI</li>
                            <li>BDI</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>C</h4>
                    <div class="country-group">
                        <h5>Cabo Verde</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Cabo Verde</span> (primary)</li>
                            <li>CV</li>
                            <li>CPV</li>
                            <li>Cape Verde</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Cambodia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Cambodia</span> (primary)</li>
                            <li>KH</li>
                            <li>KHM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Cameroon</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Cameroon</span> (primary)</li>
                            <li>CM</li>
                            <li>CMR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Central African Republic</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Central African Republic</span> (primary)</li>
                            <li>CF</li>
                            <li>CAF</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Chad</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Chad</span> (primary)</li>
                            <li>TD</li>
                            <li>TCD</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Chile</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Chile</span> (primary)</li>
                            <li>CL</li>
                            <li>CHL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Colombia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Colombia</span> (primary)</li>
                            <li>CO</li>
                            <li>COL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Comoros</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Comoros</span> (primary)</li>
                            <li>KM</li>
                            <li>COM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Costa Rica</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Costa Rica</span> (primary)</li>
                            <li>CR</li>
                            <li>CRI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Croatia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Croatia</span> (primary)</li>
                            <li>HR</li>
                            <li>HRV</li>
                            <li>Republic of Croatia</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Cuba</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Cuba</span> (primary)</li>
                            <li>CU</li>
                            <li>CUB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Cyprus</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Cyprus</span> (primary)</li>
                            <li>CY</li>
                            <li>CYP</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Czechia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Czechia</span> (primary)</li>
                            <li>CZ</li>
                            <li>CZE</li>
                            <li>Czech Republic</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>D</h4>
                    <div class="country-group">
                        <h5>Denmark</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Denmark</span> (primary)</li>
                            <li>DK</li>
                            <li>DNK</li>
                            <li>Kingdom of Denmark</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Djibouti</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Djibouti</span> (primary)</li>
                            <li>DJ</li>
                            <li>DJI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Dominica</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Dominica</span> (primary)</li>
                            <li>DM</li>
                            <li>DMA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Dominican Republic</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Dominican Republic</span> (primary)</li>
                            <li>DO</li>
                            <li>DOM</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>E</h4>
                    <div class="country-group">
                        <h5>Ecuador</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ecuador</span> (primary)</li>
                            <li>EC</li>
                            <li>ECU</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Egypt</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Egypt</span> (primary)</li>
                            <li>EG</li>
                            <li>EGY</li>
                            <li>Arab Republic of Egypt</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>El Salvador</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">El Salvador</span> (primary)</li>
                            <li>SV</li>
                            <li>SLV</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Equatorial Guinea</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Equatorial Guinea</span> (primary)</li>
                            <li>GQ</li>
                            <li>GNQ</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Eritrea</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Eritrea</span> (primary)</li>
                            <li>ER</li>
                            <li>ERI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Estonia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Estonia</span> (primary)</li>
                            <li>EE</li>
                            <li>EST</li>
                            <li>Republic of Estonia</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Eswatini</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Eswatini</span> (primary)</li>
                            <li>SZ</li>
                            <li>SWZ</li>
                            <li>Swaziland</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Ethiopia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ethiopia</span> (primary)</li>
                            <li>ET</li>
                            <li>ETH</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>F</h4>
                    <div class="country-group">
                        <h5>Fiji</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Fiji</span> (primary)</li>
                            <li>FJ</li>
                            <li>FJI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Finland</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Finland</span> (primary)</li>
                            <li>FI</li>
                            <li>FIN</li>
                            <li>Republic of Finland</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>G</h4>
                    <div class="country-group">
                        <h5>Gabon</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Gabon</span> (primary)</li>
                            <li>GA</li>
                            <li>GAB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Gambia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Gambia</span> (primary)</li>
                            <li>GM</li>
                            <li>GMB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Georgia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Georgia</span> (primary)</li>
                            <li>GE</li>
                            <li>GEO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Ghana</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ghana</span> (primary)</li>
                            <li>GH</li>
                            <li>GHA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Greece</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Greece</span> (primary)</li>
                            <li>GR</li>
                            <li>GRC</li>
                            <li>Hellenic Republic</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Grenada</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Grenada</span> (primary)</li>
                            <li>GD</li>
                            <li>GRD</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Guatemala</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Guatemala</span> (primary)</li>
                            <li>GT</li>
                            <li>GTM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Guinea</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Guinea</span> (primary)</li>
                            <li>GN</li>
                            <li>GIN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Guinea-Bissau</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Guinea-Bissau</span> (primary)</li>
                            <li>GW</li>
                            <li>GNB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Guyana</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Guyana</span> (primary)</li>
                            <li>GY</li>
                            <li>GUY</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>H</h4>
                    <div class="country-group">
                        <h5>Haiti</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Haiti</span> (primary)</li>
                            <li>HT</li>
                            <li>HTI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Honduras</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Honduras</span> (primary)</li>
                            <li>HN</li>
                            <li>HND</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Hungary</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Hungary</span> (primary)</li>
                            <li>HU</li>
                            <li>HUN</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>I</h4>
                    <div class="country-group">
                        <h5>Iceland</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Iceland</span> (primary)</li>
                            <li>IS</li>
                            <li>ISL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Indonesia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Indonesia</span> (primary)</li>
                            <li>ID</li>
                            <li>IDN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Iran</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Iran</span> (primary)</li>
                            <li>IR</li>
                            <li>IRN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Iraq</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Iraq</span> (primary)</li>
                            <li>IQ</li>
                            <li>IRQ</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Ireland</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ireland</span> (primary)</li>
                            <li>IE</li>
                            <li>IRL</li>
                            <li>Republic of Ireland</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Israel</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Israel</span> (primary)</li>
                            <li>IL</li>
                            <li>ISR</li>
                            <li>State of Israel</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Ivory Coast</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ivory Coast</span> (primary)</li>
                            <li>CI</li>
                            <li>CIV</li>
                            <li>Côte d'Ivoire</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>J</h4>
                    <div class="country-group">
                        <h5>Jamaica</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Jamaica</span> (primary)</li>
                            <li>JM</li>
                            <li>JAM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Jordan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Jordan</span> (primary)</li>
                            <li>JO</li>
                            <li>JOR</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>K</h4>
                    <div class="country-group">
                        <h5>Kazakhstan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Kazakhstan</span> (primary)</li>
                            <li>KZ</li>
                            <li>KAZ</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Kenya</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Kenya</span> (primary)</li>
                            <li>KE</li>
                            <li>KEN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Kiribati</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Kiribati</span> (primary)</li>
                            <li>KI</li>
                            <li>KIR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Kuwait</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Kuwait</span> (primary)</li>
                            <li>KW</li>
                            <li>KWT</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Kyrgyzstan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Kyrgyzstan</span> (primary)</li>
                            <li>KG</li>
                            <li>KGZ</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>L</h4>
                    <div class="country-group">
                        <h5>Lao</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Lao</span> (primary)</li>
                            <li>LA</li>
                            <li>LAO</li>
                            <li>Laos</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Latvia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Latvia</span> (primary)</li>
                            <li>LV</li>
                            <li>LVA</li>
                            <li>Republic of Latvia</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Lebanon</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Lebanon</span> (primary)</li>
                            <li>LB</li>
                            <li>LBN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Lesotho</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Lesotho</span> (primary)</li>
                            <li>LS</li>
                            <li>LSO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Liberia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Liberia</span> (primary)</li>
                            <li>LR</li>
                            <li>LBR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Libya</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Libya</span> (primary)</li>
                            <li>LY</li>
                            <li>LBY</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Liechtenstein</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Liechtenstein</span> (primary)</li>
                            <li>LI</li>
                            <li>LIE</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Lithuania</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Lithuania</span> (primary)</li>
                            <li>LT</li>
                            <li>LTU</li>
                            <li>Republic of Lithuania</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Luxembourg</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Luxembourg</span> (primary)</li>
                            <li>LU</li>
                            <li>LUX</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>M</h4>
                    <div class="country-group">
                        <h5>Madagascar</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Madagascar</span> (primary)</li>
                            <li>MG</li>
                            <li>MDG</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Malawi</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Malawi</span> (primary)</li>
                            <li>MW</li>
                            <li>MWI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Malaysia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Malaysia</span> (primary)</li>
                            <li>MY</li>
                            <li>MYS</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Maldives</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Maldives</span> (primary)</li>
                            <li>MV</li>
                            <li>MDV</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Mali</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Mali</span> (primary)</li>
                            <li>ML</li>
                            <li>MLI</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Malta</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Malta</span> (primary)</li>
                            <li>MT</li>
                            <li>MLT</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Marshall Islands</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Marshall Islands</span> (primary)</li>
                            <li>MH</li>
                            <li>MHL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Mauritania</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Mauritania</span> (primary)</li>
                            <li>MR</li>
                            <li>MRT</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Mauritius</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Mauritius</span> (primary)</li>
                            <li>MU</li>
                            <li>MUS</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Micronesia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Micronesia</span> (primary)</li>
                            <li>FM</li>
                            <li>FSM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Moldova</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Moldova</span> (primary)</li>
                            <li>MD</li>
                            <li>MDA</li>
                            <li>Republic of Moldova</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Monaco</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Monaco</span> (primary)</li>
                            <li>MC</li>
                            <li>MCO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Mongolia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Mongolia</span> (primary)</li>
                            <li>MN</li>
                            <li>MNG</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Montenegro</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Montenegro</span> (primary)</li>
                            <li>ME</li>
                            <li>MNE</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Morocco</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Morocco</span> (primary)</li>
                            <li>MA</li>
                            <li>MAR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Mozambique</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Mozambique</span> (primary)</li>
                            <li>MZ</li>
                            <li>MOZ</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Myanmar</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Myanmar</span> (primary)</li>
                            <li>MM</li>
                            <li>MMR</li>
                            <li>Burma</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>N</h4>
                    <div class="country-group">
                        <h5>Namibia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Namibia</span> (primary)</li>
                            <li>NA</li>
                            <li>NAM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Nauru</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Nauru</span> (primary)</li>
                            <li>NR</li>
                            <li>NRU</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Nepal</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Nepal</span> (primary)</li>
                            <li>NP</li>
                            <li>NPL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Nicaragua</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Nicaragua</span> (primary)</li>
                            <li>NI</li>
                            <li>NIC</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Niger</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Niger</span> (primary)</li>
                            <li>NE</li>
                            <li>NER</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Nigeria</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Nigeria</span> (primary)</li>
                            <li>NG</li>
                            <li>NGA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>North Korea</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">North Korea</span> (primary)</li>
                            <li>KP</li>
                            <li>PRK</li>
                            <li>Democratic People's Republic of Korea</li>
                            <li>DPRK</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>North Macedonia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">North Macedonia</span> (primary)</li>
                            <li>MK</li>
                            <li>MKD</li>
                            <li>Macedonia</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Norway</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Norway</span> (primary)</li>
                            <li>NO</li>
                            <li>NOR</li>
                            <li>Kingdom of Norway</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>O</h4>
                    <div class="country-group">
                        <h5>Oman</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Oman</span> (primary)</li>
                            <li>OM</li>
                            <li>OMN</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>P</h4>
                    <div class="country-group">
                        <h5>Pakistan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Pakistan</span> (primary)</li>
                            <li>PK</li>
                            <li>PAK</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Palau</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Palau</span> (primary)</li>
                            <li>PW</li>
                            <li>PLW</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Panama</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Panama</span> (primary)</li>
                            <li>PA</li>
                            <li>PAN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Papua New Guinea</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Papua New Guinea</span> (primary)</li>
                            <li>PG</li>
                            <li>PNG</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Paraguay</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Paraguay</span> (primary)</li>
                            <li>PY</li>
                            <li>PRY</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Peru</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Peru</span> (primary)</li>
                            <li>PE</li>
                            <li>PER</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Philippines</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Philippines</span> (primary)</li>
                            <li>PH</li>
                            <li>PHL</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Poland</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Poland</span> (primary)</li>
                            <li>PL</li>
                            <li>POL</li>
                            <li>Republic of Poland</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Portugal</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Portugal</span> (primary)</li>
                            <li>PT</li>
                            <li>PRT</li>
                            <li>Portuguese Republic</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>Q</h4>
                    <div class="country-group">
                        <h5>Qatar</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Qatar</span> (primary)</li>
                            <li>QA</li>
                            <li>QAT</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>R</h4>
                    <div class="country-group">
                        <h5>Romania</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Romania</span> (primary)</li>
                            <li>RO</li>
                            <li>ROU</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Rwanda</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Rwanda</span> (primary)</li>
                            <li>RW</li>
                            <li>RWA</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>S</h4>
                    <div class="country-group">
                        <h5>Saint Kitts and Nevis</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Saint Kitts and Nevis</span> (primary)</li>
                            <li>KN</li>
                            <li>KNA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Saint Lucia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Saint Lucia</span> (primary)</li>
                            <li>LC</li>
                            <li>LCA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Saint Vincent and the Grenadines</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Saint Vincent and the Grenadines</span> (primary)</li>
                            <li>VC</li>
                            <li>VCT</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Samoa</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Samoa</span> (primary)</li>
                            <li>WS</li>
                            <li>WSM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>San Marino</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">San Marino</span> (primary)</li>
                            <li>SM</li>
                            <li>SMR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Sao Tome and Principe</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Sao Tome and Principe</span> (primary)</li>
                            <li>ST</li>
                            <li>STP</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Saudi Arabia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Saudi Arabia</span> (primary)</li>
                            <li>SA</li>
                            <li>SAU</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Senegal</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Senegal</span> (primary)</li>
                            <li>SN</li>
                            <li>SEN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Serbia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Serbia</span> (primary)</li>
                            <li>RS</li>
                            <li>SRB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Seychelles</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Seychelles</span> (primary)</li>
                            <li>SC</li>
                            <li>SYC</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Sierra Leone</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Sierra Leone</span> (primary)</li>
                            <li>SL</li>
                            <li>SLE</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Slovakia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Slovakia</span> (primary)</li>
                            <li>SK</li>
                            <li>SVK</li>
                            <li>Slovak Republic</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Slovenia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Slovenia</span> (primary)</li>
                            <li>SI</li>
                            <li>SVN</li>
                            <li>Republic of Slovenia</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Solomon Islands</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Solomon Islands</span> (primary)</li>
                            <li>SB</li>
                            <li>SLB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Somalia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Somalia</span> (primary)</li>
                            <li>SO</li>
                            <li>SOM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>South Africa</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">South Africa</span> (primary)</li>
                            <li>ZA</li>
                            <li>ZAF</li>
                            <li>Republic of South Africa</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>South Sudan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">South Sudan</span> (primary)</li>
                            <li>SS</li>
                            <li>SSD</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Sri Lanka</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Sri Lanka</span> (primary)</li>
                            <li>LK</li>
                            <li>LKA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Sudan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Sudan</span> (primary)</li>
                            <li>SD</li>
                            <li>SDN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Suriname</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Suriname</span> (primary)</li>
                            <li>SR</li>
                            <li>SUR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Sweden</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Sweden</span> (primary)</li>
                            <li>SE</li>
                            <li>SWE</li>
                            <li>Kingdom of Sweden</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Syria</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Syria</span> (primary)</li>
                            <li>SY</li>
                            <li>SYR</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>T</h4>
                    <div class="country-group">
                        <h5>Taiwan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Taiwan</span> (primary)</li>
                            <li>TW</li>
                            <li>TWN</li>
                            <li>ROC</li>
                            <li>Republic of China</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Tajikistan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Tajikistan</span> (primary)</li>
                            <li>TJ</li>
                            <li>TJK</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Tanzania</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Tanzania</span> (primary)</li>
                            <li>TZ</li>
                            <li>TZA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Thailand</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Thailand</span> (primary)</li>
                            <li>TH</li>
                            <li>THA</li>
                            <li>Kingdom of Thailand</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>The Democratic Republic of the Congo</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">The Democratic Republic of the Congo</span> (primary)</li>
                            <li>CD</li>
                            <li>COD</li>
                            <li>DRC</li>
                            <li>Congo-Kinshasa</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>The Republic of the Congo</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">The Republic of the Congo</span> (primary)</li>
                            <li>CG</li>
                            <li>COG</li>
                            <li>Congo-Brazzaville</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Timor-Leste</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Timor-Leste</span> (primary)</li>
                            <li>TL</li>
                            <li>TLS</li>
                            <li>East Timor</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Togo</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Togo</span> (primary)</li>
                            <li>TG</li>
                            <li>TGO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Tonga</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Tonga</span> (primary)</li>
                            <li>TO</li>
                            <li>TON</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Trinidad and Tobago</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Trinidad and Tobago</span> (primary)</li>
                            <li>TT</li>
                            <li>TTO</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Tunisia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Tunisia</span> (primary)</li>
                            <li>TN</li>
                            <li>TUN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Turkey</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Turkey</span> (primary)</li>
                            <li>TR</li>
                            <li>TUR</li>
                            <li>Republic of Turkey</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Turkmenistan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Turkmenistan</span> (primary)</li>
                            <li>TM</li>
                            <li>TKM</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Tuvalu</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Tuvalu</span> (primary)</li>
                            <li>TV</li>
                            <li>TUV</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>U</h4>
                    <div class="country-group">
                        <h5>Uganda</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Uganda</span> (primary)</li>
                            <li>UG</li>
                            <li>UGA</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Ukraine</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Ukraine</span> (primary)</li>
                            <li>UA</li>
                            <li>UKR</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>United Arab Emirates</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">United Arab Emirates</span> (primary)</li>
                            <li>AE</li>
                            <li>ARE</li>
                            <li>UAE</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Uruguay</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Uruguay</span> (primary)</li>
                            <li>UY</li>
                            <li>URY</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Uzbekistan</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Uzbekistan</span> (primary)</li>
                            <li>UZ</li>
                            <li>UZB</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>V</h4>
                    <div class="country-group">
                        <h5>Vanuatu</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Vanuatu</span> (primary)</li>
                            <li>VU</li>
                            <li>VUT</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Venezuela</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Venezuela</span> (primary)</li>
                            <li>VE</li>
                            <li>VEN</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Vietnam</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Vietnam</span> (primary)</li>
                            <li>VN</li>
                            <li>VNM</li>
                            <li>Socialist Republic of Vietnam</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>W</h4>
                    <div class="country-group">
                        <h5>Western Sahara</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Western Sahara</span> (primary)</li>
                            <li>EH</li>
                            <li>ESH</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>Y</h4>
                    <div class="country-group">
                        <h5>Yemen</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Yemen</span> (primary)</li>
                            <li>YE</li>
                            <li>YEM</li>
                        </ul>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>Z</h4>
                    <div class="country-group">
                        <h5>Zambia</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Zambia</span> (primary)</li>
                            <li>ZM</li>
                            <li>ZMB</li>
                        </ul>
                    </div>
                    <div class="country-group">
                        <h5>Zimbabwe</h5>
                        <ul class="countries-list">
                            <li><span class="primary-name">Zimbabwe</span> (primary)</li>
                            <li>ZW</li>
                            <li>ZWE</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="noResults" class="no-results" style="display: none;">
            <div class="no-results-content">
                <h3>No countries found</h3>
                <p>No countries match your search criteria. Try searching for:</p>
                <ul>
                    <li>Country names (e.g., "Germany", "Japan")</li>
                    <li>ISO codes (e.g., "US", "DE", "JP")</li>
                    <li>Alternative names (e.g., "UK", "Holland", "Burma")</li>
                </ul>
            </div>
        </div>

        <div class="pattern-note">
            <strong>Note:</strong> Country names are case-insensitive, so "United States", "united states", and "UNITED STATES"
            are all accepted. If a country variant is not listed above, you can request it to be added by
            <a class="link" href="../community/index.php">posting a feature request</a> or by <a class="link" href="../contact-us/index.php">contacting support</a>.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('countrySearch');
            const categories = document.querySelectorAll('.countries-category');
            const noResults = document.getElementById('noResults');

            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let totalVisibleItems = 0;

                categories.forEach(category => {
                    const countryGroups = category.querySelectorAll('.country-group, .alphabet-section');
                    const countryItems = category.querySelectorAll('.countries-list li');
                    let hasVisibleItems = false;

                    // Search through individual country items
                    countryItems.forEach(item => {
                        const countryText = item.textContent.toLowerCase();
                        if (countryText.includes(searchTerm)) {
                            item.style.display = '';
                            hasVisibleItems = true;
                            totalVisibleItems++;

                            // Show the parent group
                            const parentGroup = item.closest('.country-group, .alphabet-section');
                            if (parentGroup) {
                                parentGroup.style.display = '';
                            }
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Hide groups that have no visible items
                    countryGroups.forEach(group => {
                        const visibleItems = group.querySelectorAll('.countries-list li:not([style*="display: none"])');
                        if (visibleItems.length === 0 && searchTerm !== '') {
                            group.style.display = 'none';
                        } else if (searchTerm === '') {
                            group.style.display = '';
                        }
                    });

                    // Hide category if no items match
                    if (searchTerm !== '') {
                        const visibleGroups = category.querySelectorAll('.country-group:not([style*="display: none"]), .alphabet-section:not([style*="display: none"])');
                        category.style.display = visibleGroups.length > 0 ? '' : 'none';
                    } else {
                        category.style.display = '';
                        countryGroups.forEach(group => group.style.display = '');
                        countryItems.forEach(item => item.style.display = '');
                    }
                });

                // Show/hide no results message
                if (searchTerm !== '' && totalVisibleItems === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            });
        });
    </script>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>