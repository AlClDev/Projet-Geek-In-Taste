function displayAnnotation(smileyName, smileyClass, smileyMessage){

    // Create "p" for insert text message on smiley mouse enter event
    let explainMessage = document.createElement("p")

    // Ajout des text au survol sur chaque smiley
    smileyName = document.querySelector(smileyClass);

    // Insert event
    smileyName.addEventListener("mouseenter", function (){

        // Text message
        explainMessage.textContent = smileyMessage;

        // Insert message in DOM
        document.querySelector(smileyClass).after(explainMessage);

    });

    // Delete message
    smileyName.addEventListener("mouseleave", function (){

        explainMessage.parentElement.removeChild(explainMessage);

    });

}

// Select smiley value = 1
displayAnnotation("discustingSmiley", ".la-grimace", "Dégoutant");

// Select smiley value = 2
displayAnnotation("edibleSmiley", ".la-meh", "Mangeable");

// Select smiley value = 3
displayAnnotation("goodSmiley", ".la-smile", "Bon");

// Select smiley value = 4
displayAnnotation("deliciousSmiley", ".la-grin", "Délicieux");

// Select smiley value = 5
displayAnnotation("diabolicalSmiley", ".la-grin-tongue-squint", "Diabolique");



// Allez chercher tout les smileys via class .lar
const smileys = document.querySelectorAll(".lar");


// Allez cherche l'input (la note)
const rating = document.querySelector(".user-rating-smile");


// Boucle sur les smileys + ajout events
for(smiley of smileys){

    // On ecoute le survol
    smiley.addEventListener("mouseover", function(){

        // Gère le passage en noir des smileys qui suivent celui survolé
        resetSmileys();

        this.style.color = "#dc3545";

        // L'élément précedent dans le DOM (de même niveau, balise soeur)
        let previousSmiley = this.previousElementSibling;

        // Tant qu'il y à des étoiles avant
        while(previousSmiley){

            // On les passes en rouge
            previousSmiley.style.color = "#dc3545";

            // Récupérer l'étoile qui l'as précède
            previousSmiley = previousSmiley.previousElementSibling;

        }

    });

    // On écoute au clic
    smiley.addEventListener("click", function(){

        // Récupérer la note & la placer dans l'input
        rating.value = this.dataset.value;

    });

    // Repasse toute les étoiles en oir, jusqu'a atteindre la note du champ input
    smiley.addEventListener("mouseout", function(){

        // Prendra soit 0 soit la note de l'user
        resetSmileys(rating.value);

    });

}

// Placé ici pour facilité la récupération des constantes
// Fonction mettant les smiley en noir au survol
function resetSmileys(rating = 0){

    for(smiley of smileys){

        // "enregistre" la note au clic
        if(smiley.dataset.value > rating){

            smiley.style.color = "white";

        }else{

            smiley.style.color = "#dc3545";

        }



    }

}

/* Sidebar Profil*/
function openNav() {
    document.getElementById("sideNavigation").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
    document.getElementById("sideNavigation").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
}

