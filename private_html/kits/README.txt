Put each finished guide in this folder as <slug>.pdf.

The file name must match the product's "slug" in data/products.json, for example:

    car-buying-out-the-door-guide.pdf
    assisted-living-guide.pdf

Then open data/products.json and change that product's "status" from
"coming_soon" to "live". The buy button switches on by itself.

If the PDF is missing, the buy button stays switched off even when the status
says "live", so a buyer can never pay for a file that is not here.

Nothing in this folder is reachable by a web address. Files are streamed by
public_html/api/download.php, and only after a signed, unexpired link is checked
against a paid order.
