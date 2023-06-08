-----------------------------------------------------
------------------ Products -------------------------
-----------------------------------------------------

/////////////////// Standard  ////////////////////////////////////

- endpointsComment : Si vous souhaitez mettre un commentaire personnalisé par endpoint.
					Exemple: "endpoints": [0],
							"endpointsComment": ["comment in english","commentaire en français"]
							
							Ou 

							"endpoints": [0,1],
							"endpointsComment": [["comment in english","commentaire en français"],["comment in english","commentaire en français"]]



- Pour le cluster configuration il faut choisir une ou plusieurs source(s) (cf: https://support.nke-watteco.com/configuration-cluster/).
   C'est les mêmes sources que pour le treshold et le batch.
        Exemple : "availablePowerSource":[4,2],


/////////////////// Seuil /////////////////////////////////////
- Configuration de seuils, ajout d'une propriété "treshold" dans chaques clusters.
  Cette propriété a 2 attributs, "available" qui peut prendre comme valeur, true ou false.
  Et l'attribut slot qui est un tableau. Ce tableau est composé de l'attributeID qui peut accueillir le seuil (Attention c'est case sensitive !), 
  de l'attribut nuberSlot qui définit le nombre de slot que l'on peut configurer, 
  et l'attribut fieldIndex qui est un tableau avec les différents fieldIndex. 
  Exemple:  
  "threshold" : {
                "available" : true,
                "slot" : [
                    {
                     "attribute" : "NodePowerDescriptor", 
                      "numberSlot" : 3,
                      "fieldindex" : [4,2]
                      }
                  ]

            },

/////////////////// Report interval ///////////////////////////
- Configuration de valeurs par défaut d'intervalles de reporting.
  Lors de la déclaration d'un cluster dans un fichier produit, il est possible de choisir les valeurs affichées dans les champs 
  "Maximum reporting configuration" et "Minimum reporting configuration" à l'aide de l'objet minMaxReport sous le format [[tempsMin,selectUnité],[tempsMax,selectUnité]]
  Exemple :
    "clusters": [
      {
        "clusterID": "BinaryInput",
        "endpoints": [0,1,2,3,4,5,6,7,8,9],
        "endpointsComment": ["---","---"],
        "minMaxReport":[[0,0],[60,1]],
        "threshold" : {
          "available" : false
        }
      }
    ]
  Ici, [[0,0],[60,1]] représente 0 secondes minimum et 60 minutes maximum. 
  /!\ Attention /!\ Ce paramètre n'est absolument pas recommandé à cause du batch !

////////////////////// BATCH ////////////////////////////////
- Configuration de batch, ajout d'une propriété "batch" qui est un tableau d'objets. Chaque objet peut avoir 2 ou 3 attributs.
  Le premier est "attribute", il correspond au nom de l'attribut (l'attributeID) qui pourra être configuré en mode Batch. Attention c'est case sensitive !
  Le second est "available", un boolean qui vaut true ou false.
  Le troisième est "config", il est optionnel, c'est la configuration de base qu'on peut lui attribuer.Si présent, elle n'est pas modifiable en mode simple. c'est un tableau de tableau.
  Exemple pour comprendre : "batch" : [
				{
					"attribute" : "NodePowerDescriptor", 
					"available" : true,
					"config" : [[[4,100,2,3],[2,100,3,3]]] 
				}	
				]
			Ou encore
				"batch" : 
				[
					{
						"attribute" : "PresentValue",
						"available" : true	
					},
					{
						"attribute" : "Count",
						"available" : true	
					}
				]
  A l'indice 0 du tableau sera mis le premier endpoint configurable du cluster. A l'indice 1 du tableau le second endpoint etc.. A l'indice 0 on peut retrouver des tableaux. Chaque tableau est de la forme [FieldIndex,Resolution,TagLabel,TagSize] et donc on pourra retrouver plusieurs tableaux par endpoint si il y a plusieurs Field configurables, par défaut si il n'y a pas de FieldIndex, il faut mettre 0.
  Si il n'y a pas de config, alors le Batch ne sera configurable qu'en mode advanced !
  Si il y a une config alors la résolution, le taglabel et tagsize seront configurable qu'en mode advanced.
  Tous les clusters peuvent être configuré en batch en Configure Reporting, sauf le cluster Lorawan et Basic. (il manque aussi les TICS)




-----------------------------------------------------
------------------ Clusters -------------------------
-----------------------------------------------------

- Le report Batch est le reportType 1 dans les clusters. Les spécificités se retrouvent au niveau des FieldIndex, si il y en a il faut tous les mettre et leur donner un nom qui sera affiché dans un select.
  Si il y a différents fieldIndex alors pas besoin de spécifier d'unités, de mantisses, de multipliers et de ranges. En Js on vient récupérer le fieldIndex choisit dans le select et on va directement chercher ces informations dans le reportType 0 (le standard). Si il n'y a pas de FieldIndex alors il faut mettre 0, et pas besoin de choisir un nom car le select ne s'affichera pas. 





















Ino 016 005
Presso 005
presso 0 10v
pusle 014 006
triphaso

















