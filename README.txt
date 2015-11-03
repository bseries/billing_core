Billing Core
-- Core functionality for Banque.

Synopsis
--------
This is the foundation module for all Banque modules. It covers
tax, client group and payment handling. It further includes
view helpers to format financial values and a base document 
for financial PDFs.

Payment methods are backed by Omnipay. By default the following
methods are supported out of the box:

- PayPal (Express, Pro, Rest)
- Wirecard
- Local (Invoice, Debit Card)

Authors
-------
David Persson for Atelier Disko (david@atelierdisko.de).

Copyright & License
-------------------
This library is Copyright (c) 2014 Atelier Disko - All rights 
reserved and licensed under the AD General Software License v1. 

-- This software is proprietary and confidential. Redistributions   --
-- not permitted. Unless required by applicable law or agreed to    --
-- in writing, software distributed on an "AS IS" BASIS, WITHOUT    --
-- WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. --

You should have received a copy of the AD General Software
License. If not, see http://atelierdisko.de/licenses.

This library may contain code from open source projects with a different
license. Please see the respective fileheaders for more information.

