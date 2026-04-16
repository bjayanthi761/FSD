package com.campus.eventmanagement.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import com.campus.eventmanagement.model.*;
import com.campus.eventmanagement.service.*;

@Controller
public class RegistrationController {

    @Autowired
    private RegistrationService regService;

    @Autowired
    private EventService eventService;

    @GetMapping("/register/{id}")
    public String showForm(@PathVariable Long id, Model model) {
        Registration r = new Registration();
        r.setEvent(eventService.getById(id));
        model.addAttribute("registration", r);
        return "register";
    }

    @PostMapping("/register")
    public String save(Registration r) {
        regService.save(r);
        return "redirect:/";
    }

    @GetMapping("/registrations/{id}")
    public String view(Model model) {
        model.addAttribute("list", regService.getAll());
        return "registrations";
    }
}
