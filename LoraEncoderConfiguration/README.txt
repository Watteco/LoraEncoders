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



- Pour le cluster configuration il faut choisir une ou plusieurs source(s) (cf: http://support.nke-watteco.com/configuration-cluster/).
   C'est les mêmes sources que pour le treshold et le batch.
        Exemple : "availablePowerSource":[4,2],


/////////////////// Seuil /////////////////////////////////////
- Configuration de seuils, ajout d'une propri�t� "treshold" dans chaques clusters.
  Cette propri�t� a 2 attributs, "available" qui peut prendre comme valeur, true ou false.
  Et l'attribut slot qui est un tableau. Ce tableau est compos� de l'attributeID qui peut accueillir le seuil (Attention c'est case sensitive !), de l'attribut nuberSlot qui définit le nombre de slot que l'on peut configurer, 
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

////////////////////// BATCH ////////////////////////////////
- Configuration de batch, ajout d'une propri�t� "batch" qui est un tableau d'objets. Chaque objet peut avoir 2 ou 3 attributs.
  Le premier est "attribute", il correspond au nom de l'attribut (l'attributeID) qui pourra �tre configur� en mode Batch. Attention c'est case sensitive !
  Le second est "available", un boolean qui vaut true ou false.
  Le troisi�me est "config", il est optionnel, c'est la configuration de base qu'on peut lui attribuer.Si pr�sent, elle n'est pas modifiable en mode simple. c'est un tableau de tableau.
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
  A l'indice 0 du tableau sera mis le premier endpoint configurable du cluster. A l'indice 1 du tableau le second endpoint etc.. A l'indice 0 on peut retrouver des tableaux. Chaque tableau est de la forme [FieldIndex,Resolution,TagLabel,TagSize] et donc on pourra retrouver plusieurs tableaux par endpoint si il y a plusieurs Field configurables, par d�faut si il n'y a pas de FieldIndex, il faut mettre 0.
  Si il n'y a pas de config, alors le Batch ne sera configurable qu'en mode advanced !
  Si il y a une config alors la r�solution, le taglabel et tagsize seront configurable qu'en mode advanced.
  Tous les clusters peuvent �tre configur� en batch en Configure Reporting, sauf le cluster Lorawan et Basic. (il manque aussi les TICS)




-----------------------------------------------------
------------------ Clusters -------------------------
-----------------------------------------------------

- Le report Batch est le reportType 1 dans les clusters. Les sp�cificit�s se retrouvent au niveau des FieldIndex, si il y en a il faut tous les mettre et leur donner un nom qui sera affich� dans un select.
  Si il y a diff�rents fieldIndex alors pas besoin de sp�cifier d'unit�s, de mantisses, de multipliers et de ranges. En Js on vient r�cup�rer le fieldIndex choisit dans le select et on va directement chercher ces informations dans le reportType 0 (le standard). Si il n'y a pas de FieldIndex alors il faut mettre 0, et pas besoin de choisir un nom car le select ne s'affichera pas. 





















Ino 016 005
Presso 005
presso 0 10v
pusle 014 006
triphaso

















