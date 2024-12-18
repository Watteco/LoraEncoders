//-------------------------------------------------------------------------------
// Get the list of available products
// Fills in the options and tooltips of the DOM object (select) "productSelect"
// All data comes from "AvailableProductsList.json" and is 
// dependent on the global variables "lang" and "loraEncoderType"
// An optional parameter "optSelectedIndex" can be provided to the function to 
// select an item from the list. If the parameter is missing, item 0 
// is selected
//
// Depending on the definition of WITH_GROUPS_AND_TOOLTIPS below, the options are 
// "grouped" or not according to "Category" field from JSON
//
WITH_GROUPS_AND_TOOLTIPS = true;

if (! WITH_GROUPS_AND_TOOLTIPS)
{
	getAllAvailableProducts = function getAllAvailableProducts_old(optSelectedIndex) {
		getLocalConfiguration();
		
	// Use 2nd line from FOTA LEC
		//getJSON(getRootUrl() + "/Lora/WattecoSensors/AvailableProductsList.json?v=" + (new Date()).getTime(),
	getJSON("/Lora/WattecoSensors/AvailableProductsList.json?v=" + (new Date()).getTime(),
			function(err, availableProducts) {
				if (err !== null) {
					console.log('Une erreur est survenue : ' + err);
				} else {
					var productSelect = document.getElementById("productSelect");
					
					// Add a custom field for Batch decoder
					if (loraEncoderType === "Decoder") 
					{
						const option = document.createElement('option');
						option.value = "";
						option.text = "Custom";
						option["title"] = (lang == 0 ? 
							'Enter specific batch params: <TagSize>;(<space><TagLabel>,<Resolution>,<Type>;[,<Name>])*' :
							'Entrer des param&egrave;tres batch sp&eacute;cifiques: <TagSize>;(<space><TagLabel>,<Resolution>,<Type>;[,<Nom>])*');
						option["data-tooltip"] =  (lang == 0 ? 
							'Enter specific batch params: &lt;TagSize&gt;(&lt;space&gt;&lt;TagLabel&gt;,&lt;Resolution&gt;,&lt;Type&gt;[,&lt;Name&gt;] )*' :
							'Entrer des param&egrave;tres batch sp&eacute;cifiques: &lt;TagSize&gt;(&lt;space&gt;&lt;TagLabel&gt;,&lt;Resolution&gt;,&lt;Type&gt;[,&lt;Nom&gt;])*');
						productSelect.appendChild(option);
					}

					availableProducts.products.forEach(function(product){
						displayed = 1;
						bParams = (typeof(product["BatchUncompressParams"]) !== 'undefined' ? product["BatchUncompressParams"] : "");
						if(typeof(product["apps"]) !== 'undefined') {
				// loraEncoderType defined in calling index.html ("LoraEncoder" or "LoraEncoderConfiguration")
							displayed = product.apps.includes(loraEncoderType) && ((loraEncoderType != "Decoder") || (bParams != ""));
						}
						if (displayed) {
							var opt = document.createElement('option');
							opt.appendChild(document.createTextNode((Array.isArray(product.name) ? product.name[lang] : product.name)));
							opt.value = (loraEncoderType === "Decoder" ? bParams : product.file);
							productSelect.appendChild(opt);
						}	
					});

					// Finally select eventual specific index else set index 0
					var idx = 0;
					if (optSelectedIndex != 'undefined')
					{
						if ((optSelectedIndex > 0) && (optSelectedIndex < productSelect.length))
						{
							idx = optSelectedIndex;
						}
					}
					productSelect.selectedIndex = idx;
				}
			}
		);
	}
} 
else // WITH_GROUPS_AND_TOOLTIPS 
{ 
	getAllAvailableProducts = function getAllAvailableProducts_with_groups_and_tooltips(optSelectedIndex) {
		getLocalConfiguration();
		getJSON("/Lora/WattecoSensors/AvailableProductsList.json?v=" + (new Date()).getTime(), function(err, data) {
			if (err) {
				console.error("Failed to load JSON:", err);
				return; // Early exit on error
			}

			const productSelect = document.getElementById("productSelect");
			const categorizedProducts = {}; // To hold products categorized by their category
			const uncategorizedProducts = []; // To hold uncategorized products

			// Iterate over the products and categorize them
			data.products.forEach(product => {
				displayed = 1;
				bParams = (typeof(product["BatchUncompressParams"]) !== 'undefined' ? product["BatchUncompressParams"] : "");
				if(typeof(product["apps"]) !== 'undefined') {
		// loraEncoderType defined in calling index.html ("LoraEncoder" or "LoraEncoderConfiguration")
					displayed = product.apps.includes(loraEncoderType) && ((loraEncoderType != "Decoder") || (bParams != ""));
				}
				if (displayed) 
				{
					// If the product has no category, store it in uncategorizedProducts
					if (!product.category) {
						uncategorizedProducts.push(product);
					} else {
						// Store categorized products in categorizedProducts object
						if (!categorizedProducts[product.category]) {
							categorizedProducts[product.category] = [];
						}
						categorizedProducts[product.category].push(product);
					}
				}
			});

			// Helper function for sorting with special handling for products starting with ~ or [Deprecated]
			function sortProducts(a, b) {
				const nameA = Array.isArray(a.name) ? a.name[lang] : a.name;
				const nameB = Array.isArray(b.name) ? b.name[lang] : b.name;

				// Products with ~ or [Deprecated] should come last
				const isAAtEnd = nameA.startsWith("~") || nameA.startsWith("[Deprecated]");
				const isBAtEnd = nameB.startsWith("~") || nameB.startsWith("[Deprecated]");

				// Both are end products or neither
				if (isAAtEnd && !isBAtEnd) return 1;
				if (!isAAtEnd && isBAtEnd) return -1;

				// Otherwise, sort alphabetically
				return nameA.localeCompare(nameB);
			}

			// Sort uncategorized products alphabetically, with ~ and [Deprecated] names at the end
			uncategorizedProducts.sort(sortProducts);

			function setOptionsTooltips(option, product){
				const zeComment = (product.comment ? ((Array.isArray(product.comment) ? product.comment[lang] : product.comment)) : "");
				const zeDocLink = (product.docLink ? ((Array.isArray(product.docLink) ? product.docLink[lang] : product.docLink)) : "");
				if (zeComment.length != 0)
				{
					option["title"] = zeComment;
					option["data-tooltip"] = zeComment;
				}
				if (zeDocLink.length != 0) {
					option["data-tooltip"] = option["data-tooltip"] + (lang == 0 ?
						"<br> <a href='" + zeDocLink +"' target='_blank'>  Type &lt;Enter&gt; to Learn more</a>" :
						"<br> <a href='" + zeDocLink +"' target='_blank'>  Tapper &lt;Entr&eacutee&gt; pour en savoir plus</a>")
				}
			}

			
			// Add a custom field for Batch decoder
			if (loraEncoderType === "Decoder") 
				{
					const option = document.createElement('option');
					option.value = "";
					option.text = "Custom";
					option["title"] = (lang == 0 ? 
						'Enter specific batch params: <TagSize>;(<space><TagLabel>,<Resolution>,<Type>;[,<Name>])*' :
						'Entrer des param&egrave;tres batch sp&eacute;cifiques: <TagSize>;(<space><TagLabel>,<Resolution>,<Type>;[,<Nom>])*');
					option["data-tooltip"] =  (lang == 0 ? 
						'Enter specific batch params: &lt;TagSize&gt;(&lt;space&gt;&lt;TagLabel&gt;,&lt;Resolution&gt;,&lt;Type&gt;[,&lt;Name&gt;] )*' :
						'Entrer des param&egrave;tres batch sp&eacute;cifiques: &lt;TagSize&gt;(&lt;space&gt;&lt;TagLabel&gt;,&lt;Resolution&gt;,&lt;Type&gt;[,&lt;Nom&gt;])*');
					productSelect.appendChild(option);
				}

			// Add uncategorized products directly to the combo box
			uncategorizedProducts.forEach(product => {
				const option = document.createElement('option');
				bParams = (typeof(product["BatchUncompressParams"]) !== 'undefined' ? product["BatchUncompressParams"] : "");
				option.value = (loraEncoderType === "Decoder" ? bParams : product.file);
				option.text = ((Array.isArray(product.name) ? product.name[lang] : product.name));
				setOptionsTooltips(option, product);
				productSelect.appendChild(option);
			});

			// Get the categories and sort them alphabetically
			const sortedCategories = Object.keys(categorizedProducts).sort();

			// Iterate over each sorted category
			sortedCategories.forEach(category => {
				// Create a new <optgroup> for each category
				const optgroup = document.createElement('optgroup');
				optgroup.label = category;
				optgroup.id = `optgroup-${category}`;
				productSelect.appendChild(optgroup);

				// Sort the products within the category alphabetically, with ~ and [Deprecated] names at the end
				const sortedProducts = categorizedProducts[category].sort(sortProducts);

				// Add each sorted product to the corresponding <optgroup>
				sortedProducts.forEach(product => {
					const option = document.createElement('option');
					bParams = (typeof(product["BatchUncompressParams"]) !== 'undefined' ? product["BatchUncompressParams"] : "");
					option.value = (loraEncoderType === "Decoder" ? bParams : product.file);
					option.text = ((Array.isArray(product.name) ? product.name[lang] : product.name));
					setOptionsTooltips(option, product);
					optgroup.appendChild(option);
				});
			});

			// On select box Tooltip functionality allowing an URL link on <Enter> key
			const tooltip = document.getElementById('tooltip');
		
			// Event listener to show tooltip when hovering over the select
			productSelect.addEventListener('mousemove', function(event) {
				const option = productSelect.options[productSelect.selectedIndex];
				if (option['data-tooltip']) {
					const tooltipText = option['data-tooltip'];
					if (tooltipText) {
						tooltip.style.display = 'block';
						tooltip.innerHTML = tooltipText; // Set the HTML content of the tooltip
						tooltip.style.left = event.pageX + 10 + 'px';
						tooltip.style.top = event.pageY + 10 + 'px';
					}
				}
			});
		
			// Hide the tooltip when the mouse leaves the select box
			productSelect.addEventListener('mouseleave', function() {
				tooltip.style.display = 'none';
			});

			// Handle keydown event to manage the Enter key => open the URL in tooltip 
			document.addEventListener('keydown', function(event) {
				if (event.key === 'Enter' && tooltip.style.display === 'block') {
					const link = tooltip.querySelector('a'); // Get the link in the tooltip
					if (link) {
						window.open(link.href, '_blank'); // Open the link in a new tab
					}
				}
			});

			// Finally select eventual specific index else set index 0
			var idx = 0;
			if (optSelectedIndex != 'undefined')
			{
				if ((optSelectedIndex > 0) && (optSelectedIndex < productSelect.length))
				{
					idx = optSelectedIndex;
				}
			}
			productSelect.selectedIndex = idx;
		});
	}
} // WITH_GROUPS_AND_TOOLTIPS