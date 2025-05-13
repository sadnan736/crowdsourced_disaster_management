console.log("scripgt running")
document.querySelector('#close').style.display ='none';
document.querySelector('#menu').addEventListener('click' , ()=>{
    document.querySelector('.sidebar').classList.toggle('sidebarGo')
    if(document.querySelector('.sidebar').classList.contains('sidebarGo')){
        document.querySelector("#menu").style.display = 'inline'
        document.querySelector("#close").style.display = 'none'
        
    }
    else{
        setTimeout(()=>{
            
            document.querySelector('.main').classList.add('mainBlur')
            document.querySelector("#close").style.display = 'inline'
            
        },300)
            
        document.querySelector("#menu").style.display = 'none'
    }
});
document.querySelector('#close').addEventListener('click' , ()=>{
        document.querySelector('.sidebar').classList.toggle('sidebarGo')
        if(document.querySelector('.sidebar').classList.contains('sidebarGo')){
            document.querySelector("#menu").style.display = 'inline'
            document.querySelector("#close").style.display = 'none'
            document.querySelector('.main').classList.remove('mainBlur')
        }
        else{

                
            document.querySelector("#menu").style.display = 'none'
            setTimeout(()=>{
                document.querySelector("#close").style.display = 'inline'
        
            },300)
        }

});

document.querySelector('.btn').addEventListener("click", function(){
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      const division = document.querySelector("#di").value;
      const name = document.querySelector("#nm").value;
      const number = document.querySelector("#no").value;
      const disaster = document.querySelector("#ty").value;
      
      
      console.log("Division:", division);
      console.log("Name:", name);
      console.log("Number:", number);
      console.log("Disaster:", disaster);
      

      if(!division || !name || !number || !disaster) {
        alert("Please fill all fields.");
        return;
      }

      doc.setFont("Arial", "bold");
      doc.setFontSize(20);
      doc.text("Disaster Report Form", 20, 20);

      doc.setFontSize(14);
      doc.text(`Division: ${division}`, 20, 40);
      doc.text(`Name: ${name}`, 20, 50);
      doc.text(`Number: ${number}`, 20, 60);
      doc.text(`Disaster Type: ${disaster}`, 20, 70);

      console.log("pdf:", doc);

      doc.save("Disaster_Report.pdf");
});