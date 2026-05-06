package com.campus.eventmanagement.controller;

import com.campus.eventmanagement.model.Event;
import com.campus.eventmanagement.model.Registration;
import com.campus.eventmanagement.service.EventService;
import com.campus.eventmanagement.service.RegistrationService;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
public class RegistrationController {

    @Autowired
    private EventService eventService;

    @Autowired
    private RegistrationService registrationService;

    @GetMapping("/register/{id}")
    public String showRegisterPage(@PathVariable Long id, Model model) {

        Event event = eventService.getEventById(id);

        if(event == null) {
            return "redirect:/";
        }

        Registration registration = new Registration();
        registration.setEvent(event);

        model.addAttribute("event", event);
        model.addAttribute("registration", registration);

        return "register";
    }

    @PostMapping("/register")
    public String register(@ModelAttribute Registration registration) {

        registrationService.save(registration);
        return "redirect:/";
    }

    @GetMapping("/registrations/{id}")
    public String viewRegistrations(@PathVariable Long id, Model model) {

        List<Registration> list = registrationService.getByEventId(id);

        model.addAttribute("registrations", list);

        return "registrations";
    }
}
